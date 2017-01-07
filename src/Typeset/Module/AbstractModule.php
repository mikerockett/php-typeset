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
     * The configuration for this module,
     * imported from the main configuration
     * @var mixed
     */
    protected $config;

    /**
     * The processing-result
     * @var mixed
     */
    protected $result;

    /**
     * Construct the module instance, saving
     * the configuration
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Method to obtain the result
     * @return string
     */
    public function getResult()
    {
        return (string) $this->result;
    }
}
