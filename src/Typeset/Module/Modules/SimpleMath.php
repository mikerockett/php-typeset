<?php

/**
 * PHP Typeset / SimpleMath Module
 *
 * Very simple equation formatters.
 *
 * This module is disabled by default as it is not
 * commonly used. Additionally, it's technically going
 * to remain experimental for a while.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;

class SimpleMath extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $text = preg_replace('/(\d+)\s?x\s?(\d+)/', "$1 × $2", $text);
        $text = preg_replace('/(\d+)\s?\/\s?(\d+)/', "$1 ÷ $2", $text);
        $text = preg_replace(
            '/\b(\d+)\^(\w+)\b/xu',
            sprintf('$1<sup%s>$2</sup>', empty($this->config->exponentClass) ? '' : ' class="' . $this->config->exponentClass . '"'),
            $text
        );

        $this->result = $text;
    }
}
