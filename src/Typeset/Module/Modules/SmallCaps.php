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

class SmallCaps extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $wordList = explode(' ', $text);

        foreach ($wordList as $index => $word) {
            $brokenWord = Str::removeCruft($word);
            $word = $brokenWord[1];

            if (Str::isAcronym($word)) {
                $wordList[$index] = sprintf(
                    '%s<span class="%s">%s</span>%s',
                    $brokenWord[0], $this->config->class, $word, $brokenWord[2]
                );
            }
        }

        $this->result = implode(' ', $wordList);
    }
}
