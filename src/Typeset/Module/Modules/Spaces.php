<?php

/**
 * PHP Typeset / Spaces Module
 *
 * Use thin spaces around division and multiplication
 * signs and forward slashes.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;

class Spaces extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        foreach (['÷', '×', '=', '/'] as $character) {
            $text = str_replace(
                " {$character} ", // normal spaces
                "&#8202;{$character}&#8202;", // hair spaces
                $text
            );
        }

        $this->result = $text;
    }
}
