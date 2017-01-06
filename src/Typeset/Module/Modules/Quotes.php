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

use Typeset\Module\AbstractModule;
use Typeset\Support\Str;

class Quotes extends AbstractModule
{
    const ABBREVIATED_YEARS =
        '/(\x{2018})([0-9]{2}[^\x{2019}]*)(\x{2018}([^0-9]|$)|$|\x{2019}[a-z])/iu';

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

    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $qnode = pq($node);
        if ($qnode->is('p, blockquote') && $qnode->text() !== $text) {
            $parentText = $replace($qnode->text());
            $start = 0;
            $qnode->contents()->each(function ($parentNode) use ($node, &$start) {
                if ($parentNode === $node) {
                    return false;
                }
                $start += strlen(pq($parentNode)->text());
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
            self::BACKWARDS_APOSTROPHE,
            self::TRIPLE_PRIME,
            self::DOUBLE_PRIME,
            self::SINGLE_PRIME,
            self::ESCAPED_OPEN_DOUBLE_QUOTE,
            self::ESCAPED_CLOSE_DOUBLE_QUOTE,
            self::ESCAPED_OPEN_SINGLE_QUOTE,
            self::ESCAPED_CLOSE_SINGLE_QUOTE,
        ], [
            "$1" . Str::uchr("201c") . "$2",
            "$1" . Str::uchr("201d") . "$2",
            "$1" . Str::uchr("201d"),
            "$1" . Str::uchr("2018") . "$2",
            "$1" . Str::uchr("2019") . "$2",
            "$1" . Str::uchr("2019") . "$3",
            Str::uchr("2019") . "$2$3",
            "$1" . Str::uchr("2019"),
            Str::uchr("2034"), // switch to str_replace?
            Str::uchr("2033"),
            Str::uchr("2032"), // switch to str_replace?
            '\"', // switch to str_replace?
            '\"', // switch to str_replace?
            "\'", // switch to str_replace?
            "\'", // switch to str_replace?
        ], $text);

        return $text;
    }
}
