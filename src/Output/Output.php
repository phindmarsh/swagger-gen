<?php


namespace Wave\Swagger\Generator\Output;


use Wave\Swagger\Generator\Parser\Parser;

abstract class Output {

    /** @var Parser */
    protected $parser;

    public function __construct(Parser $parser){
        $this->parser = $parser;
    }

    abstract function generate();

}