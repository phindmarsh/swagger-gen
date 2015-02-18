<?php


namespace Wave\Swagger\Generator;


use Symfony\Component\Yaml\Yaml;
use Wave\Annotation;
use Wave\Config;
use Wave\Router\Action;
use Wave\Swagger\Generator\Spec\Operation;
use Wave\Swagger\Generator\Spec\Parameter;
use Wave\Swagger\Generator\Spec\ParameterBag;
use Wave\Validator;

class FromRoutes extends AbstractGenerator {

    const INCLUDE_SCHEMA_KEY = 'x-include-schema';

    static $type_translations = array(
        'int' => 'integer',
        'string' => '%s'
    );

    protected $controller_dir;
    protected $schema_dir;
    protected $includes_dir;

    public function __construct($controller_dir, $schema_dir, $includes_dir) {
        $this->controller_dir = $controller_dir;
        $this->schema_dir = $schema_dir;
        $this->includes_dir = $includes_dir;
    }


    protected function getPathItemObjects() {

        $reflector = new \Wave\Reflector($this->controller_dir);
        $reflected_options = $reflector->execute();
        $routes = \Wave\Router\Generator::buildRoutes($reflected_options);

        /**
         * @var string $callable
         * @var Action $action
         */
        $operations = [];

        foreach($routes['default'] as $callable => $action){

            // Retrieve class and method
            list($class, $function) = explode('.', $action->getAction());

            $class = preg_replace('/^Controllers\\\\?/i', '', $class);
            $class = preg_replace('/Controller$/i', '', $class);

            foreach($action->getRoutes() as $route){

                preg_match_all('/([^\/]+)+/i', $route, $matches);
                $method = strtolower(array_shift($matches[0]));

                $in_hint = $this->getParameterInHint($method);

                $operation = new Operation(array(
                    'x-class' => $class,
                    'x-function' => $function
                ));
                $operation->tags->add($class);

                if($action->hasAnnotation('include')){
                    foreach($action->getAnnotation('include') as $include){
                        $operation->merge($this->parseIncludeFile($include->getValue(), $in_hint));
                    }
                }

                foreach($action->getAnnotations() as $key => $annotations){
                    foreach($annotations as $annotation){
                        $this->applyAnnotation($key, $annotation, $operation, $in_hint);
                    }
                }

                foreach ($matches[0] as $i => $part) {
                    if (preg_match('/<(?<type>\w+)>(?<name>\w+)/i', $part, $match)) {
                        $matches[0][$i] = sprintf('{%s}', $match['name']);
                        $parameter = array(
                            'name' => $match['name'],
                            'in' => Parameter::IN_PATH,
                            'required' => true
                        );
                        $this->convertType($match['type'], $parameter);
                        $operation->parameters->add(Parameter::factory($parameter));
                    }
                }

                $route = '/' . implode('/', $matches[0]);

                if(!array_key_exists($route, $operations))
                    $operations[$route] = array();

                $operations[$route][$method] = $operation->toArray();
            }
        }

        return $operations;

    }

    private function applyAnnotation($key, Annotation $annotation, Operation &$operation, $in_hint){

        $fix_case = array(
            'operationid' => 'operationId'
        );

        $key = isset($fix_case[$key]) ? $fix_case[$key] : $key;

        switch($key){
            case 'summary':
            case 'description':
            case 'operationId':
                $operation->$key = $annotation->getValue();
                break;
            case 'deprecated':
                $operation->$key = in_array($annotation->getValue(), array(1, true, '1', 'true'), true);
                break;
            case 'tags':
            case 'consumes':
            case 'produces':
            case 'schemes':
                $operation->$key->merge(array_map(function($v){ return trim($v); }, explode(',', $annotation->getValue())));
                break;
            case 'parameter':
                $operation->parameters->add($this->parseParameter($annotation->getValue(), $in_hint));
                break;
            case 'parameters':
            case 'validate':
                $operation->parameters->merge($this->resolveSchema($annotation->getValue(), $in_hint));
                break;

        }

    }

    /**
     * @param $schema
     * @param string $parameter_in_hint
     * @return ParameterBag
     */
    private function resolveSchema($schema, $parameter_in_hint){
        $schema_file = sprintf('%s%s.php', $this->schema_dir, $schema);
        if(!file_exists($schema_file))
            throw new \RuntimeException("Could not resolve validation schema {$schema}, looked in {$schema_file}");

        $schema = require $schema_file;
        $bag = new ParameterBag();

        foreach ($schema['fields'] as $key => $val) {
            $parameter = array(
                'name' => $key,
                'required' => isset($val['required']) && is_bool($val['required']) ? $val['required'] : false
            );
            $this->convertType(isset($val['type']) ? $val['type'] : 'string', $parameter);


            if (isset($schema['aliases'][$key])) {
                $parameter['x-alias'] = $parameter['name'];
                $parameter['name'] = $schema['aliases'][$key];
            }

            // if the schema declares an overrides array then use it as well
            if(isset($schema['swagger'][$key])){
                $parameter = array_merge($parameter, $schema['swagger'][$key]);
            }

            $bag->add(Parameter::factory($parameter, $parameter_in_hint));
        }

        return $bag;
    }

    private function parseParameter($annotation, $parameter_in_hint){

        $data = array(
            'type' => 'string',
            'required' => true
        );

        $parts = explode(' ', $annotation);
        // detect if the type was specified
        if($parts[0][0] !== '$') {
            $this->convertType(array_shift($parts), $data);
        }

        $data['name'] = substr(array_shift($parts), 1);

        if($parts[0] === '[optional]'){
            $data['required'] = false;
            array_shift($parts);
        }

        $description = trim(implode(' ', $parts));
        if(!empty($description))
            $data['description'] = $description;

        return Parameter::factory($data, $parameter_in_hint);

    }

    private function parseIncludeFile($include, $parameter_in_hint) {
        $include_file = sprintf("%s%s.yml", $this->includes_dir, $include);
        if(!file_exists($include_file))
            throw new \RuntimeException("Could not resolve swagger include {$include}, looked in {$include_file}");

        $contents = Yaml::parse(file_get_contents($include_file));

        $bag = new ParameterBag();
        // check for x-includes and things
        if(array_key_exists('parameters', $contents)){
            foreach($contents['parameters'] as $i => $parameter){
                if(array_key_exists(static::INCLUDE_SCHEMA_KEY, $parameter)){
                    $bag->merge($this->resolveSchema($parameter[static::INCLUDE_SCHEMA_KEY], $parameter_in_hint));
                }
                else {
                    $bag->add(Parameter::factory($parameter, $parameter_in_hint));
                }
            }
        }

        $operation = new Operation($contents);
        $operation->setParameters($bag);

        return $operation;
    }

    private function getParameterInHint($method){
        switch($method){
            case 'delete':
            case 'get':
                return Parameter::IN_QUERY;
            case 'post':
            case 'put':
            default:
                return Parameter::IN_BODY;
        }
    }

    private function convertType($type, array &$parameter){
        switch($type){
            case 'int':
                $parameter['type'] = 'integer';
                return;
            case 'float':
                $parameter['type'] = 'number';
                $parameter['format'] = 'float';
                return;
            case 'bool':
                $parameter['type'] = 'boolean';
                return;
            case 'string':
            case 'email':
                $parameter['type'] = 'string';
                return;
            default:
                trigger_error("Unknown type [{$type}]", E_USER_NOTICE);
                $parameter['type'] = $type;
                return;
        }
    }



}
