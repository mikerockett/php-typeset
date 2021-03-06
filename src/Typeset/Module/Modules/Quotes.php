<?php

/**
 * PHP Typeset / Quotes Module
 *
 * Handles correct replacement of straight-quotation-marks,
 * converting them to their correct, contextual equivalents.
 *
 * Processes for double quotes, single quotes, and then those
 * that remain are converted to either single or double primes.
 *
 * Allows for straight quotes to be escaped for preservation.
 */

namespace Typeset\Module\Modules;

use phpQuery;
use Typeset\Module\AbstractModule;
use Typeset\Support\Chr;

class Quotes extends AbstractModule
{
    const ABBREVIATED_YEARS =
        '/(\x{2018})([0-9]{2}[^\x{2019}]*)(\x{2018}([^0-9]|$)|$|\x{2019}[a-z])/iu';

    const CORRECT_N =
        '/(?!\s)(\x{2018})(n)(\x{2019})(?=\s)/u';

    const BACKWARDS_APOSTROPHE =
        '/(\B|^)\x{2018}(?=([^\x{2019}]*\x{2019}\b)*([^\x{2019}\x{2018}]*\W[\x{2019}\x{2018}]\b|[^\x{2019}\x{2018}]*$))/iu';

    const DOUBLE_PRIME =
        '/"|\'\'/';

    const DOUBLE_QUOTE_END =
        '/(\x{201c}[^"]*)"([^"]*$|[^\x{201c}"]*\x{201c})/u';

    const DOUBLE_QUOTE_START =
        '/(\W|^)"([^\s\!\?:;\.,‽»])/';

    const ESCAPED_CLOSE_DOUBLE_QUOTE =
        "/\\\”/";

    const ESCAPED_CLOSE_SINGLE_QUOTE =
        "/\\\‘/";

    const ESCAPED_OPEN_DOUBLE_QUOTE =
        "/\\\“/";

    const ESCAPED_OPEN_SINGLE_QUOTE =
        "/\\\’/";

    const REMAINING_DOUBLE_QUOTE_END =
        '/([^0-9])"/';

    const SINGLE_PRIME =
        '/\'/';

    const SINGLE_QUOTE_CONJUNCTION_POSSESSION =
        '/([a-z])\'([a-z])/i';

    const SINGLE_QUOTE_END =
        '/((\x{2018}[^\']*)|[a-z])\'([^0-9]|$)/iu';

    const SINGLE_QUOTE_START =
        '/(\W|^)\'(\S)/';

    const TRIPLE_PRIME =
        '/\'\'\'/';

    const PRIME_FIX =
        '/(\x{2032})\s([\d.]+\x{2033})/u';

    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $qnode = phpQuery::pq($node);
        if ($qnode->parent()->is('p, blockquote') && $qnode->parent()->text() !== $text) {
            $parentText = $this->doReplacements($qnode->parent()->text());
            $start = 0;
            $qnode->parent()->contents()->each(function ($parentNode) use ($node, &$start) {
                if ($parentNode === $node) {
                    return false;
                }
                $start += strlen(phpQuery::pq($parentNode)->text());
            });
            $this->result = substr($parentText, $start + strlen($text));
        }

        $this->result = $this->doReplacements($text);
    }

    /**
     * @param  $text
     * @return string
     */
    protected function doReplacements($text)
    {
        $text = preg_replace([
            self::DOUBLE_QUOTE_START,
            self::DOUBLE_QUOTE_END,
            self::REMAINING_DOUBLE_QUOTE_END,
            self::SINGLE_QUOTE_START,
            self::SINGLE_QUOTE_CONJUNCTION_POSSESSION,
            self::SINGLE_QUOTE_END,
            self::ABBREVIATED_YEARS,
            self::CORRECT_N,
            self::BACKWARDS_APOSTROPHE,
            self::ESCAPED_OPEN_DOUBLE_QUOTE,
            self::ESCAPED_CLOSE_DOUBLE_QUOTE,
            self::ESCAPED_OPEN_SINGLE_QUOTE,
            self::ESCAPED_CLOSE_SINGLE_QUOTE,
        ], [
            "$1" . Chr::ldquo() . "$2",
            "$1" . Chr::rdquo() . "$2",
            "$1" . Chr::rdquo(),
            "$1" . Chr::lsquo() . "$2",
            "$1" . Chr::rsquo() . "$2",
            "$1" . Chr::rsquo() . "$3",
            Chr::gets(['zwsp', 'rsquo']) . "$2$3", //  zwsp prevents HanginPunctuation
            Chr::gets(['zwsp', 'rsquo']) . "$2$3", // from wrapping these
            "$1" . Chr::rsquo(),
            '\"', // switch to str_replace?
            '\"', // switch to str_replace?
            "\'", // switch to str_replace?
            "\'", // switch to str_replace?
        ], $text);

        if ($this->config->primes) {
            $text = preg_replace([
                self::TRIPLE_PRIME,
                self::DOUBLE_PRIME,
                self::SINGLE_PRIME,
                self::PRIME_FIX,
            ], [
                Chr::tprime(), // switch to str_replace?
                Chr::dprime(),
                Chr::prime(), // switch to str_replace?
                '$1' . Chr::nbsp() . '$2',
            ], $text);
        }

        return $text;
    }
}
