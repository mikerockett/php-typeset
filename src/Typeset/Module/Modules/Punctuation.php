<?php

/**
 * PHP Typeset / Punctuation Module
 *
 * Convert hypens and double hyphens to dashes,
 * and triple-periods to ellipses.
 *
 * Insert a non-breaking-space before and after
 * specific punctuation marks.
 *
 * This module has similarities to the Symbols
 * module, and may be merged in the future.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Str;

class Punctuation extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        // Plain text replacements
        $replacements = [
            // One space after a period (browsers trim these, but this is for consistency)
            '.  ' => '. ',
            // double-hyphen -> em dash
            '--' => Str::uchr('emdash'),
            // space-wrapped em dash -> hair-space-wrapped em dash
            ' ' . Str::uchr('emdash') . ' ' => Str::uchrs(['hairspace', 'emdash', 'hairspace']),
            // space-wrapped single-hyphen -> hair-space-wrapped em dash
            ' - ' => Str::uchrs(['hairspace', 'emdash', 'hairspace']),
            // quadruple-period => period + ellipses
            '....' => '.' . Str::uchr('ellipses'),
            // triple-period => ellipses
            '...' => Str::uchr('ellipses'),
        ];
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Expression-based replacements
        $replacements = [
            // Swap invalid spaces before/after specific punctuation
            '@([«¿¡])\s+@u' => "$1" . Str::uchr('nbsp'),
            '@\s+([\!\?:;\.,‽»])@u' => Str::uchr('nbsp') . '$1',
        ];
        $text = preg_replace(array_keys($replacements), array_values($replacements), $text);

        $this->result = $text;
    }
}
