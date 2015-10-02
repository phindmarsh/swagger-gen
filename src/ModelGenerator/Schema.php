<?php
/**
 *
 * @file       Format.php
 * @package    sdk-generator
 * @author     Michael Calcinai <michael@calcin.ai>
 * @link
 *
 */

namespace Wave\SDK\ModelGenerator;


abstract class Schema {


    protected $schemas = [];


    /**
     * Add a schema by name to be used during generation
     * @param Loader $loader
     * @return
     */
    abstract public function loadSchema($name, Loader $loader);

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