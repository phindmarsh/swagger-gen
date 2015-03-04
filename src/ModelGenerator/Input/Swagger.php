<?php


namespace Wave\SDK\ModelGenerator\Input;


class Swagger extends Input {



    public function addSchema($name, $schema = null){

        if($schema === null){
            if(is_string($name) && is_readable($name)){
                $schema = json_decode(file_get_contents($name), true);
                if(json_last_error() !== JSON_ERROR_NONE){
                    $message = function_exists('json_last_error_msg')
                        ? json_last_error_msg()
                        : 'Error code: ' . json_last_error();
                    throw new \InvalidArgumentException("Could not parse schema: " . $message);
                }
            }
            else if (is_object($name)) {
                $schema = $name;
            }
            else {
                throw new \InvalidArgumentException("Can not determine name from passed schema");
            }
            $name = $schema['info']['x-name'];
        }

        parent::addSchema($name, $schema);

    }


    public function getModels($schema_name){

        if(!isset($this->schemas[$schema_name]))
            throw new \InvalidArgumentException("Unknown schema [$schema_name]");

        $schema = $this->schemas[$schema_name];

        $models = [];
        foreach($schema['paths'] as $path => $operations){
            foreach($operations as $http_method => $operation){

                $class = $operation['x-class'];
                $function = $operation['x-function'];

                if(!isset($models[$class])){
                    $models[$class] = [
                        'class_name' => $class,
                        'operations' => []
                    ];
                }

                $method = [
                    'function' => $function,
                    'method' => $http_method,
                ];

                $this->parsePath($path, $method);

                if(isset($operation['description']))
                    $method['comment'] = $operation['description'];

                if(isset($operation['parameters'])){
                    $method['parameters'] = [];
                    $this->parseParameters($operation['parameters'], $method['parameters']);
                }

                $models[$class]['operations'][$function] = $method;
            }
        }

        return $models;

    }

    private function parsePath($path, array &$method){

        $parts = preg_split('/(\{[\w]+\})/', $path, -1, PREG_SPLIT_DELIM_CAPTURE);

        $replacements = [];
        foreach($parts as $i => $part){
            if(!empty($part) && $part[0] === '{') {
                $name = substr($part, 1, -1);
                if(!isset($replacements[$name]))
                    $replacements[$name] = count($replacements) + 1;

                $parts[$i] = '%'.$replacements[$name].'$s';
            }
        }

        $method['path'] = implode('', $parts);
        if(!empty($replacements))
            $method['path_replacements'] = $replacements;

    }


    private function parseParameters($parameters, array &$method){
        foreach($parameters as $parameter){

            if(!isset($method[$parameter['in']])){
                $method[$parameter['in']] = [];
            }

            if($parameter['in'] === 'body'){
                $this->parseBodyParameter($parameter, $method[$parameter['in']]);
            }
            else {
                $method[$parameter['in']][] = [
                    'name' => $parameter['name'],
                    'required' => isset($parameter['required']) ? $parameter['required'] : false,
                    'type' => $parameter['type']
                ];
            }
        }
    }

    private function parseBodyParameter($parameter, array &$parameters){

        $properties = $parameter['schema']['properties'];
        $required = isset($parameter['required']) ? $parameter['required'] : [];

        foreach($properties as $name => $property){
            $parameters[] = [
                'name' => $name,
                'type' => $property['type'],
                'required' => in_array($name, $required, true)
            ];
        }

    }

}