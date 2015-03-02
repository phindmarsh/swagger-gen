<?php


namespace Wave\Swagger\Generator\Output;


use Wave\Swagger\Generator\Operation;
use Wave\Swagger\Generator\Parameter;

class Swagger extends Output {

    const VENDOR_EXTENSION_PREFIX = 'x-';

    public function generate(){

        $output = [
            'swagger' => '2.0',
            'info' => [
                'title' => 'An API description',
                'version' => '1.0.0'
            ],
            'paths' => []
        ];

        $operations = $this->parser->getOperations();

        foreach($operations as $path => $methods){
            if(!isset($output['paths'][$path]))
                $output['paths'][$path] = [];

            foreach($methods as $method => $operation){
                if(!isset($output['paths'][$path][$method]))
                    $output['paths'][$path][$method] = [];

                $output['paths'][$path][$method] = $this->buildOperation($operation);
            }
        }

        return $output;
    }

    private function buildOperation(Operation $operation){

        static $allowed_keys = [
            'description'
        ];

        $output = [
            'x-class' => $operation->class,
            'x-function' => $operation->function,
            'responses' => [
                'default' => [
                    'description' => 'The default response'
                ]
            ]
        ];

        foreach($operation as $key => $value){
            if(isset($operation->$key) && in_array($key, $allowed_keys))
                $output[$key] = $value;
        }

        foreach($operation->params as $in => $params){

            if(!isset($output['parameters']))
                $output['parameters'] = [];

            if($in === Parameter::IN_BODY){
                $output['parameters'][] = $this->buildBodyParameter($params);
            }
            else {
                foreach($params as $param){
                    $output['parameters'][] = $this->buildNonBodyParameter($param);
                }
            }
        }

        return $output;

    }

    /**
     * @param Parameter[] $parameters
     * @return array
     */
    private function buildBodyParameter(array $parameters){

        $schema = [
            'type' => 'object',
            'required' => [],
            'properties' => []
        ];

        foreach($parameters as $parameter){
            $built = $this->buildNonBodyParameter($parameter);
            if(isset($built['required']) && $built['required'])
                $schema['required'][] = $parameter->name;

            unset($built['name'], $built['in'], $built['required']);

            $schema['properties'][$parameter->name] = $built;
        }

        return [
            'name' => 'body',
            'in' => 'body',
            'schema' => $schema
        ];

    }

    private function buildNonBodyParameter(Parameter $parameter){

        static $allowed_keys = [
            'name', 'in', 'required', 'type'
        ];

        $output = [];
        foreach($parameter as $key => $value){
            if(in_array($key, $allowed_keys)){
                $output[$key] = $value;
            }
            else if($key[0] !== '_'){
                $output[self::VENDOR_EXTENSION_PREFIX . $key] = $value;
            }
        }


        return $output;

    }

}