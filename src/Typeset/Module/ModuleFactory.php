<?php

/**
 * PHP Typeset / Module Factory
 *
 * This factory creates instances of modules on-demand.
 */

namespace Typeset\Module;

final class ModuleFactory implements FactoryInterface
{
    /**
     * Create a module and throw an exception if it doesn't exist
     * @param $name
     * @param $config
     * @return Module extends AbstractModule
     */
    public static function createModule($name, $config)
    {
        $module = __NAMESPACE__ . "\\Modules\\{$name}";
        if (class_exists($module)) {
            return new $module($config);
        } else {
            throw new ModuleException("Module [$name] does not exist.");
        }
    }
}
