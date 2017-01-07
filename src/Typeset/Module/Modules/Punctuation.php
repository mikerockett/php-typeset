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

class Punctuation extends AbstractModule
{
    const NBSP_PUNCTUATION_END = '/ ([\!\?:;\.,‽»])/';

    const NBSP_PUNCTUATION_START = '/([«¿¡]) /';

    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        // we should probably use uchr() for this
        $text = str_replace(
            ['--', ' – ', ' - ', '...'],
            ['–', '&thinsp;&mdash;&thinsp;', '&#8202;&mdash;&#8202;', '&hellip;'],
            $text
        );

        $text = preg_replace([
            self::NBSP_PUNCTUATION_START,
            self::NBSP_PUNCTUATION_END,
        ], [
            "$1&nbsp;",
            "&nbsp;$1",
        ], $text);

        $this->result = $text;
    }
}
