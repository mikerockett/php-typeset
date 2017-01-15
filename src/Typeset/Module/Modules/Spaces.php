<?php

/**
 * PHP Typeset / Spaces Module
 *
 * Use thin spaces around division and multiplication
 * signs and forward slashes wherever they haven't
 * already been used.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Chr;

class Spaces extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        foreach ([Chr::divide(), Chr::multiply(), '=', '/'] as $character) {
            $text = str_replace(
                " {$character} ", // normal spaces
                Chr::hairspace() . $character . Chr::hairspace(),
                $text
            );
        }

        $this->result = $text;
    }
}
