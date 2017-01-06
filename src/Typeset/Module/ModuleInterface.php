<?php

/**
 * PHP Typeset / Module Interface
 *
 * Interface for modules.
 */

namespace Typeset\Module;

interface ModuleInterface
{
	public function process($text, $node);
	public function getResult();
}