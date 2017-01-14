<?php

namespace Typeset\Support;

use DOMDocument;
use Typeset\Module\ModuleException;

class Tags
{
    /**
     * Create and render an element
     * @param $name
     * @param array   $classes
     */
    public static function element($name, $content = '', $classes = [])
    {
        $dom = self::dom();
        if (empty($name)) {
            if (empty($classes)) {
                throw new ModuleException('Cannot create element without at least one class when name is not set.');
            }
            $name = array_shift($classes);
        }
        $element = $dom->createElement($name);
        if (!empty($classes)) {
            $element->setAttribute('class', implode(' ', $classes));
        }
        $element->nodeValue = $content;
        $dom->appendChild($element);

        return html_entity_decode($dom->saveHTML($element));
    }

    /**
     * Create a DOMDocument
     * @return mixed
     */
    protected static function dom()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        return $dom;
    }
}
