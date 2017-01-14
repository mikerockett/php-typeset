<?php

/**!
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
 */

namespace Typeset;

use phpQuery;
use Typeset\Module\ModuleFactory;

// We need to load this in manually so that we can retain any modifications
// made to phpQuery. This should be temporary, as the ideal alternative
// is to switch to a parsing library with full HTML5 support.
require_once __DIR__ . '/../../electrolinux/phpquery/phpQuery/phpQuery.php';

class Typeset
{
    /**
     * Modules that the input will be sent to.
     * @const array
     */
    const MODULES = [
        'Quotes',
        'Marks',
        'HangingPunctuation', // default: off
        'Symbols', // default: off
        'SmallCaps', // default: off
        'Punctuation',
        'SimpleMath', // default: off
        'Ordinals',
        'Spaces',
        'Ligatures', // default: off
    ];

    /**
     * Used to identify text nodes
     * @const int
     */
    const NODE_TEXT = 3;

    /**
     * Config assigned to an instance of Typeset.
     * Contains current defaults.
     * @var array --> stdClass
     */
    protected $config = [
        'modules' => [
            'Marks',
            'Ordinals',
            'Punctuation',
            'Quotes',
            'Spaces',
        ],
        'ignore' => [],
        'properties' => [
            'spanElement' => 'span' // blank: <first-class/> instead of <span class="first-class second..."/>
        ],
        'ordinals' => [
            'class' => 'ordinal',
        ],
        'smallCaps' => [
            'class' => 'small-caps',
        ],
        'simpleMath' => [
            'exponentClass' => 'exponent',
        ],
        'quotes' => [
            'primes' => true, // turn this off if a font does not include primes
        ]
    ];

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
     * Construct an instance of Typeset
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Merge the new config with the default config
        // Replace recursively to ensure sub-config values are
        // explicitly overwritten.
        $this->config = (object) array_replace_recursive($this->config, $config);

        // However, the modules array should completely overwrite
        // the default, provided it it a non-empty array.
        // Unlike Typeset.js, this is only an opt-in list.
        if (isset($config['modules']) && is_array($config['modules']) &&
            !empty($config['modules'])) {
            $this->config->modules = $config['modules'];
        }
    }

    /**
     * Disable one or more modules after instantiation.
     * Returns true of $modules matches result.
     * @param  $modules
     * @return bool
     */
    public function disable($modules)
    {
        return $this->toggle($modules, false) ===
            (is_string($modules) ? [$modules] : $modules);
    }

    /**
     * Enable one or more modules after instantiation.
     * Returns true of $modules matches result.
     * @param  $modules
     * @return bool
     */
    public function enable($modules)
    {
        return $this->toggle($modules, true) ===
            (is_string($modules) ? [$modules] : $modules);
    }

    /**
     * Loop through text nodes, passing them to the
     * current module for processing.
     * @param $node
     */
    public function textNodes($node)
    {
        if (phpQuery::pq($node)->is($this->ignore)) {
            return false;
        }

        phpQuery::pq($node)->contents()->each(function ($childNode) {
            if ($childNode->nodeType === self::NODE_TEXT) {
                $text = $this->escape($childNode->data);
                $childNode->data = $text;
                $moduleConfig = lcfirst($this->module);
                if (!isset($this->config->{$moduleConfig})) {
                    $this->config->{$moduleConfig} = [];
                }
                $module = ModuleFactory::createModule(
                    $this->module,
                    (object) array_merge(
                        $this->config->{$moduleConfig},
                        $this->config->properties
                    )
                );
                $module->process($text, $childNode);
                phpQuery::pq($childNode)->replaceWith($module->getResult());
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

        // Loop through each module, passing the input to it for processing.
        foreach (self::MODULES as $module) {
            // Check against the list of modules to disable
            if (isset($this->config->modules) &&
                is_array($this->config->modules) &&
                in_array($module, $this->config->modules, true)) {
                // Set the current module
                $this->module = $module;
                // Process nodes through this module
                $input = $this->nodes($input);
            }
        }

        return $input;
    }

    /**
     * Escape ampersands and arrows
     * @param  $text
     * @return string
     */
    protected function escape($text)
    {
        return str_replace(
            ['&', '<', '>', '&#39;', '&quot;'],
            ['&amp;', '&lt;', '&gt;', "'", '"'],
            $text
        );
    }

    /**
     * Initiate node traversal with phpQuery, passing each node
     * to the textNodes method for processing.
     * @param $input
     */
    protected function nodes($input)
    {
        // Set the initial elements to ignore
        $ignore =
            'head, code, pre, script, style, var, kbd, ' .
            '[class^="pull-"], [class^="push-"], ' .
            ".{$this->config->smallCaps['class']}, ' .
            '.{$this->config->ordinals['class']}";

        // If there are additional elements to ignore, pull them in
        if (isset($this->config->ignore) && is_string($this->config->ignore)) {
            $ignore .= ", {$this->config->ignore}";
        }

        $this->ignore = $ignore;

        // Send each node to the applicable module, starting from the root.
        $document = phpQuery::newDocumentHTML($input);
        $processed = $document->contents()->each([$this, 'textNodes']);

        return $processed[0] ? $processed : $input;
    }

    /**
     * Toggle module states. Used by enable() and disable().
     * Returns array of enabled/disable modules.
     * @param $modules
     * @param $enable
     * @return array
     */
    protected function toggle($modules, $enable = true)
    {
        if (is_string($modules) && !empty($modules)) {
            $modules = [$modules];
        }

        if ($enable) {
            // Union to enable
            $this->config->modules = $this->config->modules + $modules;
            $result = array_intersect($this->config->modules, $modules);
        } else {
            // Diff to disable
            $this->config->modules = array_diff($this->config->modules, $modules);
            $result = array_diff($modules, $this->config->modules);
        }

        // Return values to match $modules
        return array_values($result);
    }
}
