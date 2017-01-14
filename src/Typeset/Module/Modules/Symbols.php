<?php

/**
 * PHP Typeset / Symbols Module
 *
 * Basic symbol conversion.
 * Disabled by default - experimental, and not all fonts support these
 * Examples:
 *     It was No. 17 on the list. => It was № 17 on the list.
 *     In S 12 of the charter, ... => In § 12 of the charter, ...
 *     Then, in S 13-54, ... => Then, in §§ 13–54, ...
 */

namespace Typeset\Module\Modules;

use Typeset\Module\AbstractModule;
use Typeset\Support\Str;

class Symbols extends AbstractModule
{
    /**
     * @param  $text
     * @param  $node
     * @return string
     */
    public function process($text, $node)
    {
        // Define each symbol combination expression
        $symbols = [
            '/(?!\s)?(?:N|n)o\.\s(\d+)/' =>
            ['numero', Str::uchr('numero') . ' $1'],
            '/(\w+)\?\!/' =>
            ['interrobang', '$1' . Str::uchr('interrobang')],
            '/(?!\s)?(?:SS?)\s([A-Z\d()\].]+)-([A-Z\d.]+)/' =>
            ['silcrow', Str::uchrs(['silcrow', 'silcrow', 'nbsp']) . '$1' . Str::uchr('endash') . '$2'],
            '/(?!\s)?(?:S)\s([A-Z\d()\].]+[^-])/' =>
            ['silcrow', Str::uchrs(['silcrow', 'nbsp']) . '$1'],
        ];

        foreach ($symbols as $plain => $replacement) {
            if (isset($this->config->disable) &&
                is_array($this->config->disable) &&
                in_array($replacement[0], $this->config->disable, true)) {
                continue;
            }
            $text = preg_replace($plain, $replacement[1], $text);
        }

        $this->result = $text;
    }
}
