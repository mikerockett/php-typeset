<?php

/**
 * PHP Typeset / Factory Interface
 *
 * Interface for factories.
 */

namespace Typeset\Module;

interface FactoryInterface
{
	public static function createModule($name, $config);
}