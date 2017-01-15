<?php

/**
 * PHP Typeset / SmallCaps Module
 *
 * Wraps capital letters in <span/> tags
 * for CSS formatting.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Chr;
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
         * Expression courtesy PHP Typography
         * @author Jeffrey D. King; Peter Putzer
         * @license GNU GPL 2+ (PHP Typography package)
         * @see https://github.com/mundschenk-at/wp-typography/blob/master/php-typography/class-settings.php#L684
         *
         * @var string
         */
        $expression = '@(?<![\w\-_' . Chr::gets(['zwsp', 'shyphen']) . '])' .
        '((?:[0-9]+(?:\-|_|' . Chr::zwsp() . '|' . Chr::shyphen() . ')*' .
        '[A-ZÀ-ÖØ-Ý](?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' . Chr::zwsp() . '|' . Chr::shyphen() . ')*' .
        ')|(?:[A-ZÀ-ÖØ-Ý](?:[A-ZÀ-ÖØ-Ý]|[0-9])(?:[A-ZÀ-ÖØ-Ý]|[0-9]|\-|_|' .
        Chr::zwsp() . '|' . Chr::shyphen() . ')*' .
        '))(?![\w\-_' . Chr::zwsp() . Chr::shyphen() . '])@u';

        $this->result = preg_replace(
            $expression,
            Tags::element($this->config->spanElement, '$1', [$this->config->class]),
            $text
        );
    }
}
