<?php

/**
 * PHP Typeset / Module Interface
 *
 * Interface for modules.
 */

namespace Typeset\Module;

interface ModuleInterface
{
	/**
	 * Fetch and return the result of the process
	 * @return string
	 */
    public function getResult();

    /**
     * Process the current node
     * @param $text
     * @param $node
     */
    public function process($text, $node);
}
