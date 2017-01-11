<?php

/**
 * PHP Typeset / HangingPunctuation Module
 *
 * Wrap hanging punctuation in <span/> tags
 * for CSS styling.
 *
 * Processes for single and double quotation-marks.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;

class HangingPunctuation extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        if (strlen($text) < 2) {
            return $text;
        }

        $doubleWidth = [
            '&quot;', '"', "“", "„", "”", "&ldquo;", "&OpenCurlyDoubleQuote;",
            "&#8220;", "&#x0201C;", "&rdquor;", "&rdquo;", '&CloseCurlyDoubleQuote;',
            '&#8221;', '&ldquor;', '&bdquo;', '&#8222;',
        ];
        $singleWidth = [
            "'", '&prime;', '&apos;', '&lsquo;', '&rsquo;', '‘', '’',
        ];

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
                        $insert = $this->wrapSpan('pull', $type, $$widthChar);
                        if (isset($words[$index - 1])) {
                            $words[$index - 1] = $words[$index - 1] . $this->wrapSpan('push', $type);
                        } else if ($this->hasAdjacentText($node)) {
                            $insert = $this->wrapSpan('push', $type) . $insert;
                        }

                        $words[$index] = $insert . substr($word, strlen($$widthChar));
                    }
                }
            }
        }

        $this->result = implode(' ', $words);
    }

    /**
     * Check f the current node has adjacent text nodes
     * @param  $node
     * @return bool
     */
    protected function hasAdjacentText($node)
    {
        /*
         *  Original description from David:
         *
         *  the nearest sibling to this text node
         *  you can have two adjacent text nodes
         *  since they'd jsut be one node.
         *  however, the previous sibling could end with a text node
         *  if so, we need to add the spacer to prevent overlap
         */

        $qnode = pq($node);
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
    }

    /**
     * Wrap the mark or null in a span tag.
     * @param  $type
     * @param  $class
     * @param  $content
     * @return string
     */
    protected function wrapSpan($type, $class, $content = '')
    {
        return sprintf(
            '<span class="%s-%s">%s</span>',
            $type, $class, $content
        );
    }
}
