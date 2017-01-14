<?php

/**
 * PHP Typeset / SmallCaps Module
 *
 * Wraps capital letters in <span/> tags
 * for CSS formatting.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Str;
use Typeset\Support\Tags;

class SmallCaps extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        /**
         * Expression courtesy PHP Typography by Peter Putzer
         * @var string
         * @license GNU GPL 2+ (PHP Typography package)
         *
         * @see https://github.com/mundschenk-at/wp-typography/blob/master/php-typography/class-settings.php#L684
         */
        $expression = '@(?<![\w\-_' . Str::uchrs(['zwsp', 'shyphen']) . '])' .
        '((?:[0-9]+(?:\-|_|' . Str::uchr('zwsp') . '|' . Str::uchr('shyphen') . ')*' .
        '[A-ZÀ-ÖØ-Ý](?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . Str::uchr('zwsp') . '|' . Str::uchr('shyphen') . ')*' .
        ')|(?:[A-ZÀ-ÖØ-Ý](?:[A-ZÀ-ÖØ-Ý]|[0-9])(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' .
        Str::uchr('zwsp') . '|' . Str::uchr('shyphen') . ')*' .
        '))(?![\w\-_' . Str::uchr('zwsp') . Str::uchr('shyphen') . '])@u';

        $this->result = preg_replace(
            $expression,
            Tags::element($this->config->spanElement, '$1', [$this->config->class]),
            $text
        );
    }
}
