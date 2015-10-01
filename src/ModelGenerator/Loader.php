<?php
/**
 * @file       Loader.php
 * @package    sdk-generator
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Wave\SDK\ModelGenerator;


abstract class Loader {



    public static function create(array $config){

        switch($config['type']){
            case 'git':
                return new Loader\Git($config);
            case 'file':
                return new Loader\File($config);
            default:
                throw new \InvalidArgumentException(sprintf('[%s] is not a supported loader type'));
        }


    }

    abstract public function getContent();

}