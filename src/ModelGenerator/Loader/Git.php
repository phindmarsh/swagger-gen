<?php
/**
 * @package    sdk-generator
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Wave\SDK\ModelGenerator\Loader;

use Wave\SDK\ModelGenerator\Loader;

class Git extends Loader {

    private $repository;
    private $file;

    /**
     * Git constructor.
     * @param array $config
     */
    public function __construct($config) {
        $this->repository = $config['repository'];
        $this->file = $config['file'];
    }

    public function getContent() {

        $command = sprintf('git archive --remote=%s HEAD %s | tar -xO', $this->repository, $this->file);
        return trim(shell_exec($command));
    }
}