<?php
/**
 * @package    sdk-generator
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Wave\SDK\ModelGenerator\Loader;

use Wave\SDK\ModelGenerator\Loader;

class File extends Loader {

    private $file;

    /**
     * File constructor.
     * @param array $config
     */
    public function __construct($config) {
        $this->file = $config['file'];
    }

    public function getContent() {
        // TODO: Implement getContent() method.
    }
}