<?php

/**
 * PHP Typeset / Ligatures Module
 *
 * Convert common ligatures in the case that a font
 * does not display them normally.
 *
 * DEPRECATION NOTICE:
 * This module will be removed in a future release.
 * Browsers support ligatures in a proper manner.
 * These ligatures are not available in all fonts,
 * and so usage of this module is discouraged.
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;

class Ligatures extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        $text = str_replace('fi', 'ﬁ', $text);
        $text = str_replace('fl', 'ﬂ', $text);

        $this->result = $text;
    }
}
