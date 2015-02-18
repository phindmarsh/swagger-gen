<?php


namespace Wave\Swagger\Generator;


abstract class AbstractGenerator {

    abstract protected function getPathItemObjects();

    public function generateSchema(){

        $paths = $this->getPathItemObjects();

        return array(
            'swagger' => '2.0',
            'info' => array(
                'title' => 'An API description',
                'version' => '1.0.0'
            ),
            'paths' => $paths
        );

    }

}