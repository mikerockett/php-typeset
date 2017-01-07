<?php

/**
 * PHP Typeset / Factory Interface
 *
 * Interface for factories.
 */

namespace Typeset\Module;

interface FactoryInterface
{
	/**
     * Create a module and throw an exception if it doesn't exist
     * @param $name
     * @param $config
     * @return Module extends AbstractModule
     */
    public static function createModule($name, $config);
}
