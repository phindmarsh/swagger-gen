<?php


namespace Wave\Swagger\Generator\Spec;

/**
 * Class Parameter
 * @package Wave\Swagger\Generator\Spec
 *
 * @property string $name
 * @property string[] names list of name and aliases for this property
 * @property string $in
 * @property string $type
 *
 */
class Parameter extends AbstractDefinition {

    const TYPE_ARRAY = 'array';

    const IN_PATH = 'path';
    const IN_QUERY = 'query';
    const IN_BODY = 'body';
    const IN_GUESS = 'guess';

    protected $in_hint = self::IN_BODY;

    /**
     * Common properties of every parameter
     * @var array
     */
    protected $common_properties = array(
        'name' => 'name',
        'description' => 'description',
        'in' => 'in',
        'required' => 'required'
    );

    /**
     * Depending on body/non-body, these properties could be
     * overloaded to somewhere else (into 'schema' on body parameters)
     *
     * @var array
     */
    protected $shim_properties = array(
        'type' => 'type',
        'format' => 'format',
        'default' => 'default',
        'maximum' => 'maximum',
        'exclusiveMaximum' => 'exclusiveMaximum',
        'minumum' => 'minumum',
        'exclusiveMinimum' => 'exclusiveMinimum',
        'maxLength' => 'maxLength',
        'minLength' => 'minLength',
        'pattern' => 'pattern',
        'maxItems' => 'maxItems',
        'minItems' => 'minItems',
        'uniqueItems' => 'uniqueItems',
        'enum' => 'enum'
    );

    public static function factory(array $parameter = array(), $in_hint = self::IN_BODY){

        $in = isset($parameter['in']) ? $parameter['in'] : $in_hint;
        $class = "\\Wave\\Swagger\\Generator\\Spec\\Parameter\\";
        if($in === self::IN_BODY)
            $class .= "BodyParameter";
        else
            $class .= "NonBodyParameter";

        return new $class($parameter, $in_hint);
    }

    public function __construct(array $parameter = array(), $in_hint = self::IN_BODY) {

        $this->in_hint = $in_hint;
        $this->in = isset($parameter['in']) ? $parameter['in'] : self::IN_GUESS;

        foreach($parameter as $key => $value){
            $this->$key = $value;
        }

    }


    public function merge(Parameter $parameter) {

        foreach(array_keys($this->common_properties) as $key){
            $value = $parameter->$key;
            if(($key === 'in' && $value === self::IN_GUESS)
                || $this->$key !== null
                || $value === null) continue;

            $this->$key = $value;
        }

        foreach(array_keys($this->shim_properties) as $key){
            $value = $parameter->key;
            if($value === null) continue;

            $this->$key = $value;
        }

        return $this;
    }

    public function getNames(){
        $names = array($this->name);
        if($this->hasAlias())
            $names[] = $this->getAlias();

        return $names;
    }

    public function getAlias(){
        if($this->hasAlias())
            return $this->_data['x-alias'];
        else
            return null;
    }

    public function setAlias($alias){
        $this->_data['x-alias'] = $alias;
    }

    public function hasAlias(){
        return isset($this->_data['x-alias']);
    }

    public function toArray() {
        $exported = parent::toArray();

        if(!isset($exported['in']) || empty($exported['in']) || $exported['in'] === self::IN_GUESS){
            $exported['in'] = $this->in_hint;
        }

        if(isset($exported['type']) && $exported['type'] === self::TYPE_ARRAY && !isset($exported['items'])){
            $exported['items'] = array(
                'type' => 'string'
            );
        }

        return $exported;
    }


}