<?php

/**
 * PHP Typeset -----------------------------------------------------------------------
 *
 * Typeset is an HTML pre-processor for web typography. It provides correct
 * quotation substitution, small-caps coversion, hyphenation, ligatures, hanging-
 * punctuation, space substitution, and more.
 *
 * Typeset would not be possible without the work of:
 *
 * 1. Bram Stein: https://github.com/bramstein
 * 2. Dr Drang: http://leancrew.com/all-this/2010/11/smart-quotes-in-javascript/).
 *
 * @author David Merfield: https://github.com/davidmerfield
 * @license CC0-1.0: https://creativecommons.org/publicdomain/zero/1.0/
 *
 * PHP Typeset is a port of Typeset to PHP. It retains all features, excluding
 * hyphenation, which should be left up to the browser due to performance issues.[1]
 *
 * It also adds other features, all of which may be found in the MODULES constant
 * in the main class below.
 *
 * [1] If you would like to use hyphenation in the browser, feel free to use Hyper
 *     by Bram Stein: https://github.com/bramstein/hypher
 *
 * @author Mike Rockett: https://github.com/mikerockett
 * @license CC0-1.0: https://creativecommons.org/publicdomain/zero/1.0/
 *
 * Usage: ----------------------------------------------------------------------------
 *
 * 1. Create a new Typeset object. Note that 'hanging_punctuation'
 *    and 'capitals_numbers' is disabled by default, for performance reasons.
 *    You can also opt to ignore specific elements by means of a CSS selector.
 *
 *      $typeset = new Typeset(); // or
 *      $typeset = new Typeset([]); // to enable all features, or
 *      $typeset = new Typeset([
 *          'disable' => ['hanging_punctuation'], // array to disable a module, or
 *          'ignore' => '.skip, #anything, .which-matches', // to ignore elements.
 *      ]);
 *
 * 2. (Optional) Rename the classes that Typeset gives to span elements:
 *
 *      $typeset->classCapitals = 'small-caps';
 *      $typeset->classNumber = 'numerics';
 *      $typeset->classOrdinal = 'ord';
 *
 * 3. Typeset your HTML!
 *
 *      $html = $typeset->typeset($html);
 */

class Typeset
{
    /**
     * Modules that the input will be sent to.
     * - 'hyphenate' removed.
     * - 'small-caps' renamed to 'capitals_numbers' as it introduces number-wrapping
     *     (perhaps this module can be simplified to reduce processing time...)
     * - 'ordinals' added.
     * - 'simple_math' added.
     * @const array
     */
    const MODULES = [
        'quotes',
        'capitals_numbers', // default: off
        'ligatures',
        'punctuation',
        'hanging_punctuation', // default: off
        'simple_math', // default: off
        'ordinals',
        'spaces',
    ];

    /**
     * Capitals class
     * @var string
     */
    public $classCapitals = 'capitals';

    /**
     * Number CSS class
     * @var string
     */
    public $classNumber = 'number';

    /**
     * Ordinal CSS class
     * @var string
     */
    public $classOrdinal = 'ordinal';

    /**
     * Exponent CSS class
     * @var string
     */
    public $classExponent = 'exponent';

    /**
     * Current ignore rule
     * @var string
     */
    protected $ignore = '';

    /**
     * Current module
     * @var string
     */
    protected $module = '';

    /**
     * Options assigned to an instance of Typeset
     * @var array --> stdClass
     */
    protected $options = [];

    /**
     * Construct an instance of Typeset
     * @param array $options
     */
    public function __construct($options = ['disable' => [
        'capitals_numbers',
        'hanging_punctuation',
        'simple_math',
    ]])
    {
        // Merge the new options with the default options
        $this->options = (object) array_merge($this->options, $options);
    }

    /**
     * Loop through text nodes, passing them to the
     * current module for processing.
     * @param $node
     */
    public function textNodes($node)
    {
        if ($this->q($node)->is($this->ignore)) {
            return false;
        }

        $this->q($node)->contents()->each(function ($childNode) {
            if ($childNode->nodeType === 3) {
                $text = $this->_escape($childNode->data);
                $text = str_replace(['&#39;', '&quot;'], ["'", '"'], $text);
                $childNode->data = $text;
                $module = 'do' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->module)));
                $this->q($childNode)->replaceWith($this->$module($text, $childNode));
            } else {
                $this->textNodes($childNode);
            }
        });
    }

    /**
     * Process the input HTML and return the typeset-HTML
     * @param  $input
     * @return string
     */
    public function typeset($input)
    {
        // If the input is empty, we don't need to continue.
        if (empty($input)) {
            return;
        }

        // Pull in phpQuery
        require_once __DIR__ . '/phpQuery/phpQuery.php';

        // Loop through each module, passing the input to it for processing.
        // Unlike Typeset.js, our modules are currently methods of this class.
        foreach (self::MODULES as $module) {
            // Check against the list of modules to disable
            if (isset($this->options->disable) &&
                is_array($this->options->disable) &&
                in_array($module, $this->options->disable, true)) {
                continue;
            }

            $this->module = $module;

            // If we're good to go, then process...
            $input = $this->nodes($input);
        }

        return $input;
    }

    /**
     * Escape ampersands and arrows
     * @param $text
     * @return string
     */
    protected function _escape($text)
    {
        // return $text;
        return str_replace(
            ['&', '<', '>'],
            ['&amp;', '&lt;', '&gt;'],
            $text
        );
    }

    /**
     * @param $text
     * @return string
     */
    protected function doCapitalsNumbers($text)
    {
        $isAcronym = function ($word) {
            $onlyNumbers = '/^\d+$/';
            return (
                strlen($word) &&
                strlen(trim($word)) > 1 &&
                !preg_match($onlyNumbers, preg_replace('/[\.,-\/#!–$%°\^&\*;?:+′|@\[\]{}=\-_`~()]/', '', $word)) &&
                preg_replace('/[\.,-\/#!$%\^&\*;–?:+|@\[\]{}=\-_`~(′°)]/', '', $word) === $word &&
                strcmp(strtoupper($word), $word) === 0
            );
        };

        $removeCruft = function ($word) {
            $ignore = array_merge(preg_split("//u", "{}()-‘’[]!#$*&;:,.“”″′‘’\"'°", -1, PREG_SPLIT_NO_EMPTY), ['&quot;', "'s", "’s", '&#39;s']);
            $encodedIgnore = $ignore;

            foreach ($encodedIgnore as $key => $value) {
                $encodedIgnore[$key] = htmlspecialchars($encodedIgnore[$key]);
            }

            $ignore = array_merge($ignore, $encodedIgnore);

            $trailing = '';
            $leading = '';

            for ($i = 0; $i < count($ignore); $i++) {
                $ignoreThis = $ignore[$i];
                $endOfWord = substr($word, -strlen($ignoreThis));

                if ($endOfWord === $ignoreThis) {
                    $trailing = $ignoreThis . $trailing;
                    $word = substr($word, 0, -strlen($ignoreThis));
                    $i = 0;
                    continue;
                }
            }

            for ($j = 0; $j < count($ignore); $j++) {
                $ignoreThis = $ignore[$j];
                $startOfWord = substr($word, 0, strlen($ignoreThis));

                if ($startOfWord === $ignoreThis) {
                    $leading .= $ignoreThis;
                    $word = substr($word, strlen($ignoreThis));
                    $j = 0;
                    continue;
                }
            }

            return [$leading, $word, $trailing];
        };

        $wordList = explode(' ', $text);

        foreach ($wordList as $index => $word) {
            $brokenWord = $removeCruft($word);
            $word = $brokenWord[1];
            $leading = $brokenWord[0];
            $trailing = $brokenWord[2];

            $wrapWord = function ($class) use (&$wordList, $index, $leading, $word, $trailing) {
                $wordList[$index] = sprintf(
                    '%s<span class="%s">%s</span>%s',
                    $leading, $this->$class, $word, $trailing
                );
            };

            if ($isAcronym($word)) {
                $wrapWord('classCapitals');
            } else if (ctype_digit(str_replace(['.'], '', $word))) {
                if (isset($this->options->capitals_numbers) &&
                    is_array($this->options->capitals_numbers) &&
                    !in_array('disable_numbers', $this->options->capitals_numbers, true)) {
                    $wrapWord('classNumber');
                }
            }

        }

        return implode(' ', $wordList);
    }

    /**
     * Wrap hanging punctuation for CSS styling.
     * @param  $text
     * @return string
     */
    protected function doHangingPunctuation($text, $node)
    {
        if (strlen($text) < 2) {
            return $text;
        }

        $span = function ($type, $className, $content = '') {
            return sprintf(
                '<span class="%s-%s">%s</span>',
                $type, $className, $content
            );
        };

        $doubleWidth = [
            '&quot;', '"', "“", "„", "”", "&ldquo;", "&OpenCurlyDoubleQuote;",
            "&#8220;", "&#x0201C;", "&rdquor;", "&rdquo;", '&CloseCurlyDoubleQuote;',
            '&#8221;', '&ldquor;', '&bdquo;', '&#8222;',
        ];
        $singleWidth = [
            "'", '&prime;', '&apos;', '&lsquo;', '&rsquo;', '‘', '’',
        ];

        $hasAdjacentText = function ($node) {
            // the nearest sibling to this text node
            // you can have two adjacent text nodes
            // since they'd jsut be one node.
            // however, the previous sibling could end with a text node
            // if so, we need to add the spacer to prevent overlap
            $qnode = $this->q($node);
            if ($qnode->prev() && $qnode->prev()->children() && count($qnode->prev()->children())) {
                $lastChild = substr($node->prev->children, -1)[0];
                if ($lastChild && $lastChild->type === 'text') {
                    return true;
                }
            }

            if (!$qnode->parent() || count(!$qnode->parent())) {
                return false;
            }

            $parentPrev = $qnode->parent()[0]->prev;

            // Ensure the parent has text content
            // and is not simply a new line separating tags
            if ($parentPrev && $parentPrev->type === 'text' && trim($parentPrev->data)) {
                return true;
            }

            return false;
        };

        // Create and array of distinct words.
        $words = mb_split('\s', $text);

        // Iterate through each word ...
        foreach ($words as $index => $word) {
            // Iterate through each type (single, double) ...
            foreach (['single', 'double'] as $type) {
                $width = "{$type}Width";
                $widthChar = "{$width}Char";
                // Iterate through each type item ...
                foreach ($$width as $$widthChar) {
                    // Add pull span and add push span if there is adjacent text.
                    if (substr($word, 0, strlen($$widthChar)) === $$widthChar) {
                        $insert = $span('pull', $type, $$widthChar);
                        if (isset($words[$index - 1])) {
                            $words[$index - 1] = $words[$index - 1] . $span('push', $type);
                        } else if ($hasAdjacentText($node)) {
                            $insert = $span('push', $type) . $insert;
                        }

                        $words[$index] = $insert . substr($word, strlen($$widthChar));
                    }
                }
            }
        }

        $text = implode(' ', $words);

        return $text;
    }

    /**
     * Convert common ligatures in the case that a font
     * does not display them normally.
     * @param  $text
     * @return string
     */
    protected function doLigatures($text)
    {
        $text = str_replace('fi', 'ﬁ', $text);
        $text = str_replace('fl', 'ﬂ', $text);

        return $text;
    }

    /**
     * Wrap ordinals in sup tags
     * @param  $text
     * @return string
     */
    protected function doOrdinals($text)
    {
        $text = preg_replace(
            '/\b(\d+)(st|nd|rd|th)\b/',
            sprintf('$1<sup%s>$2</sup>', empty($this->classOrdinal) ? '' : ' class="' . $this->classOrdinal . '"'),
            $text
        );

        return $text;
    }

    /**
     * Convert hypens and double hyphens to dashes,
     * and triple-periods to ellipses.
     * @param $text
     * @param $node
     * @return string
     */
    protected function doPunctuation($text, $node)
    {
        $text = str_replace('--', '–', $text);
        $text = str_replace(' – ', '&thinsp;&mdash;&thinsp;', $text);
        $text = str_replace('...', '…', $text);

        $nbsp = '&nbsp;';
        $nbspPunctuationStart = '/([«¿¡]) /';
        $nbspPunctuationEnd = '/ ([\!\?:;\.,‽»])/';

        $text = preg_replace($nbspPunctuationStart, "$1{$nbsp}", $text);
        $text = preg_replace($nbspPunctuationEnd, "{$nbsp}$1", $text);

        return $text;
    }

    /**
     * Convert straight quotation marks to their proper
     * quotation-equivalents, and convert any remaining
     * marks to single and double primes. Allows for
     * straight quotations to be escaped for preservation.
     * @param $text
     * @param $node
     * @return string
     */
    protected function doQuotes($text, $node)
    {
        $replace = function ($text) {
            // beginning ":
            $text = preg_replace('/(\W|^)"([^\s\!\?:;\.,‽»])/', "$1" . $this->uchr("201c") . "$2", $text);
            // ending ":
            $text = preg_replace('/(\x{201c}[^"]*)"([^"]*$|[^\x{201c}"]*\x{201c})/u', "$1" . $this->uchr("201d") . "$2", $text);
            // remaining " at end of word:
            $text = preg_replace('/([^0-9])"/', "$1" . $this->uchr("201d"), $text);
            // beginning ':
            $text = preg_replace('/(\W|^)\'(\S)/', "$1" . $this->uchr("2018") . "$2", $text);
            // conjunction's possesion:
            $text = preg_replace('/([a-z])\'([a-z])/i', "$1" . $this->uchr("2019") . "$2", $text);
            // ending ':
            $text = preg_replace('/((\x{2018}[^\']*)|[a-z])\'([^0-9]|$)/iu', "$1" . $this->uchr("2019") . "$3", $text);
            // abbreviated years, like '97:
            $text = preg_replace('/(\x{2018})([0-9]{2}[^\x{2019}]*)(\x{2018}([^0-9]|$)|$|\x{2019}[a-z])/iu', $this->uchr("2019") . "$2$3", $text);
            // backwards apostrophe:
            $text = preg_replace('/(\B|^)\x{2018}(?=([^\x{2019}]*\x{2019}\b)*([^\x{2019}\x{2018}]*\W[\x{2019}\x{2018}]\b|[^\x{2019}\x{2018}]*$))/iu', "$1" . $this->uchr("2019"), $text);
            // triple-prime
            $text = preg_replace('/\'\'\'/', $this->uchr("2034"), $text);
            // double-prime
            $text = preg_replace('/"|\'\'/', $this->uchr("2033"), $text);
            // single-prime
            $text = preg_replace('/\'/', $this->uchr("2032"), $text);
            // allow escaped quotes
            $text = preg_replace("/\\\“/", '\"', $text);
            $text = preg_replace("/\\\”/", '\"', $text);
            $text = preg_replace("/\\\’/", "\'", $text);
            $text = preg_replace("/\\\‘/", "\'", $text);

            return $text;
        };

        $qnode = $this->q($node);
        if ($qnode->is('p, blockquote') && $qnode->text() !== $text) {
            $parentText = $replace($qnode->text());
            $start = 0;
            $qnode->contents()->each(function ($parentNode) use ($node, &$start) {
                if ($parentNode === $node) {
                    return false;
                }
                $start += strlen($this->q($parentNode)->text());
            });
            return substr($parentText, $start + strlen($text));
        }

        return $replace($text);
    }

    /**
     * !!
     * Very simple equation formatters.
     * This module is disabled by default as it is not
     * commonly used. Additionally, it's technically going
     * to remain experimental for a while.
     * @param  $text
     * @return string
     */
    protected function doSimpleMath($text)
    {
        $text = preg_replace('/(\d+)\s?x\s?(\d+)/', "$1 × $2", $text);
        $text = preg_replace('/(\d+)\s?\/\s?(\d+)/', "$1 ÷ $2", $text);
        $text = preg_replace(
            '/\b(\d+)\^(\w+)\b/xu',
            sprintf('$1<sup%s>$2</sup>', empty($this->classExponent) ? '' : ' class="' . $this->classExponent . '"'),
            $text
        );

        return $text;
    }

    /**
     * Use thin spaces around division and multiplication
     * signs and forward slashes.
     * @param  $text
     * @return string
     */
    protected function doSpaces($text)
    {
        foreach (['÷', '×', '=', '/'] as $character) {
            $text = str_replace(
                " {$character} ", // normal spaces
                "&#8202;{$character}&#8202;", // hair spaces
                $text
            );
        }

        return $text;
    }

    /**
     * Initiate node traversal with phpQuery, passing each node
     * to the findTextNodes method for processing.
     * @param $input
     */
    protected function nodes($input)
    {
        // Set the initial elements to ignore
        $ignore =
            'head, code, pre, script, style, var, kbd,' .
            '[class^="pull-"], [class^="push-"],' .
            ".{$this->classCapitals}, .{$this->classNumber}, .{$this->classOrdinal}";

        // If there are additional elements to ignore, pull them in
        if (isset($this->options->ignore) && is_string($this->options->ignore)) {
            $ignore .= ", {$this->options->ignore}";
        }

        $this->ignore = $ignore;

        // Send each node to the applicable module, starting from the root.
        phpQuery::$debug = false;
        $document = phpQuery::newDocumentHTML($input);
        $processed = $document->contents()->each([$this, 'textNodes']);

        return $processed[0] ? $processed : $input;
    }

    /**
     * Instead of a global pq(), run a query through $this->q().
     * @param $arg
     * @param $context
     */
    protected function q($arg, $context = null)
    {
        $args = func_get_args();
        return call_user_func_array(['phpQuery', 'pq'], $args);
    }

    /**
     * Obtain a unicode character by code.
     * @param $str
     */
    protected function uchr($str)
    {
        return html_entity_decode(preg_replace('/([\da-fA-F]{4})/', '&#x$1;', $str));
    }
}
