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
use Typeset\Support\Str;

class Spaces extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        foreach ([Str::uchr('divide'), Str::uchr('multiply'), '=', '/'] as $character) {
            $text = str_replace(
                " {$character} ", // normal spaces
                Str::uchr('hairspace') . $character . Str::uchr('hairspace'),
                $text
            );
        }

        $this->result = $text;
    }
}
