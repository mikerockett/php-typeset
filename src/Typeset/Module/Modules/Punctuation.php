<?php

/**
 * PHP Typeset / Punctuation Module
 *
 * Contextually convert hyphens to their
 * associated en/em dashes.
 * Prevent phone numbers from breaking to a
 * new line.
 * Use en dashes for numeric ranges.
 * Correct basic punctuation (periods, ellipses)
 *
 * Dash methodology courtesy PHP Typography (with several modifications):
 * @author Jeffrey D. King; Peter Putzer
 * @license GNU GPL 2+ (PHP Typography package)
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Chr;

class Punctuation extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        if (in_array('dashes', $this->config->features)) {
            // First, let's decide on dash-wrapping.
            // Chr::gets() will not wrap the dash unless
            // a valid unicode code or name is provided.
            // So we can pass null or blank to prevent wrapping.
            $dashWrapper = $this->config->parentheticalDashWrapper;
            $emDash = Chr::emdash($dashWrapper);
            $enDash = Chr::endash($dashWrapper);

            // Parenthetical em dashes (triple; spaced double/single)
            $text = str_replace('---', $emDash, $text); // These are never space-wrapped (?)
            $text = preg_replace($this->spacedDash('double'), $emDash, $text);
            $text = preg_replace($this->spacedDash('single'), $emDash, $text);

            // Parenthetical en dashes
            $anyLatinChar = Chr::ANY_LATIN_CHAR;
            $text = str_replace('--', $enDash, $text);
            $expressions = [
            	"/(\A|\s)\-([\w|{$anyLatinChar}])/u",
            	"/([\w|{$anyLatinChar}])\-(\Z|" . Chr::thinspace() . '|' . Chr::hairspace() . '|' . Chr::nnbsp() . ")/u"
            ];
            $text = preg_replace($expressions, $enDash, $text);
        }

        // Internationalised domain names: revert punycode
        $text = str_replace('xn' . Chr::endash(), 'xn--', $text);

        // Use non-breaking hyphens in phone numbers.
        //
        // Note that this does not intent to cover all the different
        // types, but only the most common. Please submit a PR if
        // you think this can be improved upon. Take note, however,
        // that the objective here is to use as few preg calls as
        // possible so as to keep performance at a high.
        $nbhyphen = Chr::nbhyphen();
        $nbsp = Chr::nbsp();
        if (in_array('phoneNumbers', $this->config->features)) {
            // US 1-(3)-3-4
            $text = preg_replace('/\b(\d)-(\(\d{3}\)|\d{3})-(\d{3})-(\d{4})\b/', "$1{$nbhyphen}$2{$nbhyphen}$3{$nbhyphen}$4", $text);
            // US [2-9]2-3-4
            $text = preg_replace('/\b([2-9]\d{2})-(\d{3})-(\d{4})\b/', "$1{$nbhyphen}$2{$nbhyphen}$3", $text);
            // Multi (3) 3-4 with space correction
            $text = preg_replace('/\b(\(\d{3}\))\s?(\d{3})-(\d{4})\b/', "$1{$nbsp}$2{$nbhyphen}$3", $text);
            // Multi 3-3-4
            $text = preg_replace('/\b(\d{3})-(\d{3})-(\d{4})\b/', "$1{$nbhyphen}$2{$nbhyphen}$3", $text);
            // Multi 3-4|5 (local)
            $text = preg_replace('/\b(\d{3})-(\d{4,5})\b/', "$1{$nbhyphen}$2", $text);
        }

        // We can sort out numeric ranges now.
        if (in_array('numericRanges', $this->config->features)) {
            $zeroWidthEnDash = Chr::endash('zwnbsp');
            $text = preg_replace('/(?<=[\d\s]|^)-(?=[\d\s]|$)/', $zeroWidthEnDash, $text);

            // Now lets revert hyphenated dates - these
            // should also use a non-breaking hyphen to avoid
            // unintentional line wrapping.
            $year = "((?:[1]{1}[9]{1}[9]{1}\d{1})|(?:[2-9]{1}\d{3}))";
            $month = "([0,1]?\d{1})";
            $day = "((?:[0-2]?\d{1})|(?:[3][0,1]{1}))";
            $text = preg_replace(
                "/\b{$year}{$zeroWidthEnDash}{$month}{$zeroWidthEnDash}{$day}\b/u",
                "$1{$nbhyphen}$2{$nbhyphen}$3", $text
            ); // yyyy-mm-dd
            $text = preg_replace(
                "/\b{$year}{$zeroWidthEnDash}{$month}\b/u",
                "$1{$nbhyphen}$2", $text
            ); // yyyy-mm
            $text = preg_replace(
                "/\b{$day}{$zeroWidthEnDash}{$month}{$zeroWidthEnDash}{$year}\b/u",
                "$1{$nbhyphen}$2{$nbhyphen}$3", $text
            ); // dd-mm-yyyy
            $text = preg_replace(
                "/\b{$month}{$zeroWidthEnDash}{$day}{$zeroWidthEnDash}{$year}\b/u",
                "$1{$nbhyphen}$2{$nbhyphen}$3", $text
            ); // mm-dd-yyyy
        }

        // Basic and follow-up replacements
        if (in_array('periodsEllipses', $this->config->features)) {
            $ellipses = Chr::ellipses();
            $replacements = [
                // One space after a period (browsers trim these, but this is for consistency)
                '.  ' => '. ',
                // Quadruple-period => period + ellipses
                '....' => ".{$ellipses}",
                '. . . .' => ".{$ellipses}",
                // Triple-period => ellipses
                '...' => $ellipses,
                '. . .' => $ellipses,
            ];
            $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        }

        // Expression-based corrections
        $replacements = [
            // Use non-breaking spaces before/after specific punctuation marks.
            '/([«¿¡])\s+/u' => "$1{$nbsp}",
            '/\s+([\!\?:;\.,‽»])/u' => "{$nbsp}$1",
        ];
        $text = preg_replace(array_keys($replacements), array_values($replacements), $text);

        $this->result = $text;
    }

    /**
     * Expression to test for all single and double
     * parenthetical dashes surrounded by any type of whitespace.
     * @param $type
     */
    protected function spacedDash($type)
    {
        $type = ($type === 'double') ? '--' : '-';
        return "/(\s|" . Chr::ALL_SPACES . "){$type}(\s|" . Chr::ALL_SPACES . ")/xui";
    }
}
