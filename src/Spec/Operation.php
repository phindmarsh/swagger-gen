<?php

namespace Wave\Swagger\Generator\Spec;

/**
 * Class Operation
 * @package Wave\Swagger\Generator\Spec
 *
 * @property GenericBag $tags
 * @property ParameterBag $parameters
 */
class Operation extends AbstractDefinition {

    public function __construct(array $operation = array()) {

        $this->setTags(new GenericBag());
        $this->setParameters(new ParameterBag());
        $this->responses = (array(
            'default' => array(
                'description' => 'The default response',
                'schema' => array(
                    'type' => 'object'
                )
            )
        ));

        foreach($operation as $property => $value){
            $this->$property = $value;
        }

    }

    public function setTags($tags){

        if(is_array($tags)){
            $tags = new GenericBag($tags);
        }
        else if(!$tags instanceof GenericBag){
            trigger_error("Operation::\tags must be set to a GenericBag or an array", E_USER_ERROR);
            return false;
        }

        $this->_data['tags'] = $tags;

        return true;
    }

    /**
     * @param ParameterBag|array $value
     * @return bool
     */
    public function setParameters($value){

        if(is_array($value)){
            $value = new ParameterBag($value);
        }
        else if(!$value instanceof ParameterBag){
            trigger_error("Operation::\$parameters must be set to a ParameterBag or an array", E_USER_ERROR);
            return false;
        }

        $this->_data['parameters'] = $value;

        return true;
    }

    /**
     * Replace properties of this operation with ones from another
     * @param Operation $operation
     */
    public function merge(Operation $operation) {

        foreach(array_keys($operation->_data) as $property){
            $value = $operation->$property;
            if($this->$property instanceof GenericBag){

                if(!$value instanceof GenericBag)
                    $value = new GenericBag($value);

                $this->$property->merge($value);
            }
            else {
                $this->$property = $value;
            }

        }

    }

}