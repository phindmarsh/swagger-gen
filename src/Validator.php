<?php


namespace Wave\Swagger\Generator;


use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;

class Validator {

    private $validation_schema;

    public function __construct($validation_schema_file = null){

        if(!file_exists($validation_schema_file))
            throw new \RuntimeException("Validation schema {$validation_schema_file} not found");

        $retreiver = new UriRetriever();
        $resolver = new RefResolver($retreiver);

        $schema = $retreiver->retrieve($validation_schema_file, 'file://');
        $resolver->resolve($schema);

        $this->validation_schema = $schema;

    }

    public function validate($swagger_schema){

        $validator = new \JsonSchema\Validator();
        $validator->check($swagger_schema, $this->validation_schema);

        if(!$validator->isValid()){
            return $validator->getErrors();
        }
        else return true;

    }



}