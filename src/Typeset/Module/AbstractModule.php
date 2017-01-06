<?php

/**
 * PHP Typeset / Abstract Module
 *
 * Provides the basis for a module
 */

namespace Typeset\Module;

abstract class AbstractModule implements ModuleInterface
{
    /**
     * @var mixed
     */
    protected $result;

    protected $config;

    public function __construct($config)
    {
    	$this->config = $config;
    }

    public function getResult()
    {
        return (string) $this->result;
    }
}
