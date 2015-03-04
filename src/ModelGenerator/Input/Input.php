<?php

namespace Wave\SDK\ModelGenerator\Input;


abstract class Input {


    protected $schemas = [];


    /**
     * Add a schema by name to be used during generation
     *
     * @param $name
     * @param $schema
     */
    public function addSchema($name, $schema){

        if(is_string($schema) && file_exists($schema)){
            $schema = json_decode(file_get_contents($schema), true);
        }

        $this->schemas[$name] = $schema;

    }

    /**
     * Return a schema by name
     *
     * @return array
     */
    public function getSchemaNames(){
        return array_keys($this->schemas);
    }

    /**
     * Return a given schema as a standardised array structure that looks something like:
     * class[]
     *   operations[]
     *     function(string)
     *     method(string)
     *     path(string)
     *     parameters[]
     *       path[]
     *       query[]
     *       body[]
     *         name(string)
     *         required(bool)
     *
     * @param string $schema_name
     * @return array
     */
    abstract public function getModels($schema_name);

}