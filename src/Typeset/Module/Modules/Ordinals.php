<?php

/**
 * PHP Typeset / Ordinals Module
 *
 * Wrap ordinals in <sup/> tags
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;

class Ordinals extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $text = preg_replace(
            '/\b(\d+)(st|nd|rd|th)\b/',
            sprintf('$1<sup%s>$2</sup>', empty($this->config->class) ? '' : ' class="' . $this->config->class . '"'),
            $text
        );

        $this->result = $text;
    }
}
