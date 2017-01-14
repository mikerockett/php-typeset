<?php
/**
 * phpQuery is a server-side, chainable, CSS3 selector driven
 * Document Object Model (DOM) API based on jQuery JavaScript Library.
 *
 * @version 0.9.5
 * @package phpQuery
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @link http://code.google.com/p/phpquery/
 * @link http://phpquery-library.blogspot.com/
 * @link http://jquery.com/
 */

define('DOMDOCUMENT', 'DOMDocument');
define('DOMELEMENT', 'DOMElement');
define('DOMNODELIST', 'DOMNodeList');
define('DOMNODE', 'DOMNode');

require_once dirname(__FILE__) . '/DOMEvent.php';
require_once dirname(__FILE__) . '/DOMDocumentWrapper.php';
require_once dirname(__FILE__) . '/phpQueryEvents.php';
require_once dirname(__FILE__) . '/Callback.php';
require_once dirname(__FILE__) . '/phpQueryObject.php';
require_once dirname(__FILE__) . '/mbstring.compat.php';

/**
 * Static namespace for phpQuery functions.
 *
 * @package phpQuery
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
abstract class phpQuery
{
    /**
     * @var mixed
     */
    public static $debug = false;

    /**
     * @var string
     */
    public static $defaultCharset = 'UTF-8';

    /**
     * Applies only to HTML.
     * @var unknown_type
     */
    public static $defaultDoctype = '<!DOCTYPE html>';

    /**
     * @var mixed
     */
    public static $defaultDocumentID = null;

    /**
     * @var array
     */
    public static $documents = [];

    /**
     * @var int
     */
    public static $dumpCount = 0;

    /**
     * @var array
     */
    public static $extendMethods = [];

    /**
     * @TODO implement
     */
    public static $extendStaticMethods = [];

    /**
     * @var mixed
     */
    public static $lastModified = null;

    /**
     * XXX: Workaround for mbstring problems
     * @var bool
     */
    public static $mbstringSupport = true;

    /**
     * Static namespace for plugins.
     * @var object
     */
    public static $plugins = [];

    /**
     * List of loaded plugins.
     * @var unknown_type
     */
    public static $pluginsLoaded = [];

    /**
     * @var array
     */
    public static $pluginsMethods = [];

    /**
     * @var array
     */
    public static $pluginsStaticMethods = [];

    /**
     * @param  $DOMNodeList
     * @return mixed
     */
    public static function DOMNodeListToArray($DOMNodeList)
    {
        $array = [];
        if (!$DOMNodeList) {
            return $array;
        }

        foreach ($DOMNodeList as $node) {
            $array[] = $node;
        }

        return $array;
    }

    /**
     * @param $method
     * @param $params
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array(
            [phpQuery::$plugins, $method],
            $params
        );
    }

    /**
     * @param $m
     */
    public static function _markupToPHPCallback($m)
    {
        return "<" . "?php " . htmlspecialchars_decode($m[1]) . " ?" . ">";
    }

    /**
     * @param  $php
     * @param  $charset
     * @return mixed
     */
    public static function _phpToMarkupCallback($php, $charset = 'utf-8')
    {
        return $m[1] . $m[2] .
        htmlspecialchars("<" . "?php" . $m[4] . "?" . ">", ENT_QUOTES | ENT_NOQUOTES, $charset) .
            $m[5] . $m[2];
    }

    /**
     *
     * @param  $callback         Callback
     * @param  $params
     * @param  $paramStructure
     * @return unknown_type
     */
    public static function callbackRun($callback, $params = [], $paramStructure = null)
    {
        if (!$callback) {
            return;
        }

        if ($callback instanceof CallbackParameterToReference) {
            // TODO support ParamStructure to select which $param push to reference
            if (isset($params[0])) {
                $callback->callback = $params[0];
            }

            return true;
        }
        if ($callback instanceof Callback) {
            $paramStructure = $callback->params;
            $callback = $callback->callback;
        }
        if (!$paramStructure) {
            return call_user_func_array($callback, $params);
        }

        $p = 0;
        foreach ($paramStructure as $i => $v) {
            $paramStructure[$i] = $v instanceof CallbackParam
            ? $params[$p++]
            : $v;
        }
        return call_user_func_array($callback, $paramStructure);
    }

    /**
     *
     * @param  $type
     * @param  $code
     * @return string
     */
    public static function code($type, $code)
    {
        return "<$type><!-- " . trim($code) . " --></$type>";
    }

    /**
     * @param  $node
     * @param  $name
     * @param  $data
     * @param  $documentID
     * @return mixed
     */
    public static function data($node, $name, $data, $documentID = null)
    {
        if (!$documentID)
        // TODO check if this works
        {
            $documentID = self::getDocumentID($node);
        }

        $document = phpQuery::$documents[$documentID];
        $node = self::dataSetupNode($node, $documentID);
        if (!isset($node->dataID)) {
            $node->dataID = ++phpQuery::$documents[$documentID]->uuid;
        }

        $id = $node->dataID;
        if (!isset($document->data[$id])) {
            $document->data[$id] = [];
        }

        if (!is_null($data)) {
            $document->data[$id][$name] = $data;
        }

        if ($name) {
            if (isset($document->data[$id][$name])) {
                return $document->data[$id][$name];
            }

        } else {
            return $id;
        }

    }

    /**
     * @param $text
     */
    public static function debug($text)
    {
        if (self::$debug) {
            print var_dump($text);
        }

    }

    /**
     * @link http://docs.jquery.com/Utilities/jQuery.each
     *
     * @param  $object
     * @param  $callback
     * @return unknown_type
     */
    public static function each($object, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        $paramStructure = null;
        if (func_num_args() > 2) {
            $paramStructure = func_get_args();
            $paramStructure = array_slice($paramStructure, 2);
        }
        if (is_object($object) && !($object instanceof Iterator)) {
            foreach (get_object_vars($object) as $name => $value) {
                phpQuery::callbackRun($callback, [$name, $value], $paramStructure);
            }

        } else {
            foreach ($object as $name => $value) {
                phpQuery::callbackRun($callback, [$name, $value], $paramStructure);
            }

        }
    }

    /**
     * Extend class namespace.
     *
     * @TODO support string $source
     * @param  string|array   $target
     * @param  array          $source
     * @return unknown_type
     */
    public static function extend($target, $source)
    {
        switch ($target) {
            case 'phpQueryObject':
                $targetRef = &self::$extendMethods;
                $targetRef2 = &self::$pluginsMethods;
                break;
            case 'phpQuery':
                $targetRef = &self::$extendStaticMethods;
                $targetRef2 = &self::$pluginsStaticMethods;
                break;
            default:
                throw new Exception("Unsupported \$target type");
        }
        if (is_string($source)) {
            $source = [$source => $source];
        }

        foreach ($source as $method => $callback) {
            if (isset($targetRef[$method])) {
                self::debug("Duplicate method '{$method}', can\'t extend '{$target}'");
                continue;
            }
            if (isset($targetRef2[$method])) {
                self::debug("Duplicate method '{$method}' from plugin '{$targetRef2[$method]}',"
                    . " can\'t extend '{$target}'");
                continue;
            }
            $targetRef[$method] = $callback;
        }
        return true;
    }

    /**
     * Get DOMDocument object related to $source.
     * Returns null if such document doesn't exist.
     *
     * @param  $source  DOMNode|phpQueryObject|string
     * @return string
     */
    public static function getDOMDocument($source)
    {
        if ($source instanceof DOMDOCUMENT) {
            return $source;
        }

        $source = self::getDocumentID($source);
        return $source
        ? self::$documents[$id]['document']
        : null;
    }

    /**
     * Returns document with id $id or last used as phpQueryObject.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     * Chainable.
     *
     * @see phpQuery::selectDocument()
     *
     * @param  unknown_type                                                                        $id
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function getDocument($id = null)
    {
        if ($id) {
            phpQuery::selectDocument($id);
        } else {
            $id = phpQuery::$defaultDocumentID;
        }

        return new phpQueryObject($id);
    }

    /**
     * Returns source's document ID.
     *
     * @param  $source  DOMNode|phpQueryObject
     * @return string
     */
    public static function getDocumentID($source)
    {
        if ($source instanceof DOMDOCUMENT) {
            foreach (phpQuery::$documents as $id => $document) {
                if ($source->isSameNode($document->document)) {
                    return $id;
                }

            }
        } else if ($source instanceof DOMNODE) {
            foreach (phpQuery::$documents as $id => $document) {
                if ($source->ownerDocument->isSameNode($document->document)) {
                    return $id;
                }

            }
        } else if ($source instanceof phpQueryObject) {
            return $source->getDocumentID();
        } else if (is_string($source) && isset(phpQuery::$documents[$source])) {
            return $source;
        }

    }

    /**
     *
     * @link http://docs.jquery.com/Utilities/jQuery.grep
     *
     * @param  $array
     * @param  $callback
     * @param  $invert
     * @return unknown_type
     */
    public static function grep($array, $callback, $invert = false)
    {
        $result = [];
        foreach ($array as $k => $v) {
            $r = call_user_func_array($callback, [$v, $k]);
            if ($r === !(bool) $invert) {
                $result[] = $v;
            }

        }
        return $result;
    }

    /**
     * @param $value
     * @param $array
     */
    public static function inArray($value, $array)
    {
        return in_array($value, $array);
    }

    /**
     *
     * @TODO there are problems with non-static methods, second parameter pass it
     *     but doesnt verify is method is really callable
     * @param  $function
     * @return unknown_type
     */
    public static function isFunction($function)
    {
        return is_callable($function);
    }

    /**
     * Checks if $input is HTML string, which has to start with '<'.
     *
     * @todo still used ?
     * @deprecated
     * @param  String $input
     * @return Bool
     */
    public static function isMarkup($input)
    {
        return !is_array($input) && substr(trim($input), 0, 1) == '<';
    }

    /**
     * Reuses existing DOMDocument object.
     * Chainable.
     *
     * @TODO support DOMDocument
     * @param  $document                                                                           DOMDocument
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function loadDocument($document)
    {
        // TODO
        die('TODO loadDocument');
    }

    /**
     *
     * @link http://docs.jquery.com/Utilities/jQuery.makeArray
     *
     * @return unknown_type
     */
    public static function makeArray($obj)
    {
        $array = [];
        if (is_object($object) && $object instanceof DOMNODELIST) {
            foreach ($object as $value) {
                $array[] = $value;
            }

        } else if (is_object($object) && !($object instanceof Iterator)) {
            foreach (get_object_vars($object) as $name => $value) {
                $array[0][$name] = $value;
            }

        } else {
            foreach ($object as $name => $value) {
                $array[0][$name] = $value;
            }

        }
        return $array;
    }

    /**
     *
     * @link http://docs.jquery.com/Utilities/jQuery.map
     */
    public static function map($array, $callback, $param1 = null, $param2 = null, $param3 = null)
    {
        $result = [];
        $paramStructure = null;
        if (func_num_args() > 2) {
            $paramStructure = func_get_args();
            $paramStructure = array_slice($paramStructure, 2);
        }
        foreach ($array as $v) {
            $vv = phpQuery::callbackRun($callback, [$v], $paramStructure);
            if (is_array($vv)) {
                foreach ($vv as $vvv) {
                    $result[] = $vvv;
                }

            } else if ($vv !== null) {
                $result[] = $vv;
            }
        }
        return $result;
    }

    /**
     * Converts document markup containing PHP code generated by phpQuery::php()
     * into valid (executable) PHP code syntax.
     *
     * @param  string|phpQueryObject $content
     * @return string                PHP code.
     */
    public static function markupToPHP($content)
    {
        if ($content instanceof phpQueryObject) {
            $content = $content->markupOuter();
        }

        /* <php>...</php> to <?php...? > */
        $content = preg_replace_callback(
            '@<php>\s*<!--(.*?)-->\s*</php>@s',
            ['phpQuery', '_markupToPHPCallback'],
            $content
        );
        /* <node attr='< ?php ? >'> extra space added to save highlighters */
        $regexes = [
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^"]*)"@s',
        ];
        foreach ($regexes as $regex) {
            while (preg_match($regex, $content));
        }

        $content = preg_replace_callback(
            $regex,
            create_function('$m',
                'return $m[1].$m[2].$m[3]."<?php "
                            .str_replace(
                                array("%20", "%3E", "%09", "&#10;", "&#9;", "%7B", "%24", "%7D", "%22", "%5B", "%5D"),
                                array(" ", ">", "   ", "\n", "  ", "{", "$", "}", \'"\', "[", "]"),
                                htmlspecialchars_decode($m[4])
                            )
                            ." ?>".$m[5].$m[2];'
            ),
            $content
        );
        return $content;
    }

    /**
     * Merge 2 phpQuery objects.
     * @protected
     * @todo node lists, phpQueryObject
     * @param array $one
     * @param array $two
     */
    public static function merge($one, $two)
    {
        $elements = $one->elements;
        foreach ($two->elements as $node) {
            $exists = false;
            foreach ($elements as $node2) {
                if ($node2->isSameNode($node)) {
                    $exists = true;
                }

            }
            if (!$exists) {
                $elements[] = $node;
            }

        }
        return $elements;
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocument($markup = null, $contentType = null)
    {
        if (!$markup) {
            $markup = '';
        }

        $documentID = phpQuery::createDocumentWrapper($markup, $contentType);
        return new phpQueryObject($documentID);
    }

    /**
     * Creates new document from file $file.
     * Chainable.
     *
     * @param  string                                                                              $file URLs allowed.
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFile($file, $contentType = null)
    {
        $documentID = self::createDocumentWrapper(
            file_get_contents($file), $contentType
        );
        return new phpQueryObject($documentID);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileHTML($file, $charset = null)
    {
        $contentType = $charset
        ? ";charset=$charset"
        : '';
        return self::newDocumentFile($file, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFilePHP($file, $contentType = null)
    {
        return self::newDocumentPHP(file_get_contents($file), $contentType);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileXHTML($file, $charset = null)
    {
        $contentType = $charset ? ";charset=$charset" : '';
        return self::newDocumentFile($file, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentFileXML($file, $charset = null)
    {
        $contentType = $charset ? ";charset=$charset" : '';
        return self::newDocumentFile($file, "text/xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentHTML($markup = null, $charset = null)
    {
        $contentType = $charset ? ";charset=$charset" : '';
        return self::newDocument($markup, "text/html{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentPHP($markup = null, $contentType = "text/html")
    {
        // TODO pass charset to phpToMarkup if possible (use DOMDocumentWrapper function)
        $markup = phpQuery::phpToMarkup($markup, self::$defaultCharset);
        return self::newDocument($markup, $contentType);
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentXHTML($markup = null, $charset = null)
    {
        $contentType = $charset ? ";charset=$charset" : '';
        return self::newDocument($markup, "application/xhtml+xml{$contentType}");
    }

    /**
     * Creates new document from markup.
     * Chainable.
     *
     * @param  unknown_type                                                                        $markup
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
     */
    public static function newDocumentXML($markup = null, $charset = null)
    {
        $contentType = $charset ? ";charset=$charset" : '';
        return self::newDocument($markup, "text/xml{$contentType}");
    }

    /**
     *
     * @param  $code
     * @return string
     */
    public static function php($code)
    {
        return self::code('php', $code);
    }

    /**
     * @param  $php
     * @param  $charset
     * @return mixed
     */
    public static function phpToMarkup($php, $charset = 'utf-8')
    {
        $regexes = [
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)<' . '?php?(.*?)(?:\\?>)([^\']*)\'@s',
            '@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)<' . '?php?(.*?)(?:\\?>)([^"]*)"@s',
        ];
        foreach ($regexes as $regex) {
            while (preg_match($regex, $php, $matches)) {
                $php = preg_replace_callback(
                    $regex,
                    ['phpQuery', '_phpToMarkupCallback'],
                    $php
                );
            }
        }

        $regex = '@(^|>[^<]*)+?(<\?php(.*?)(\?>))@s';
        $php = preg_replace($regex, '\\1<php><!-- \\3 --></php>', $php);
        return $php;
    }

    /**
     * Extend phpQuery with $class from $file.
     *
     * @param string $class Extending class name. Real class name can be prepended phpQuery_.
     * @param string $file  Filename to include. Defaults to "{$class}.php".
     */
    public static function plugin($class, $file = null)
    {
        if (in_array($class, self::$pluginsLoaded)) {
            return true;
        }

        if (!$file) {
            $file = $class . '.php';
        }

        $objectClassExists = class_exists('phpQueryObjectPlugin_' . $class);
        $staticClassExists = class_exists('phpQueryPlugin_' . $class);
        if (!$objectClassExists && !$staticClassExists) {
            require_once $file;
        }

        self::$pluginsLoaded[] = $class;
        // static methods
        if (class_exists('phpQueryPlugin_' . $class)) {
            $realClass = 'phpQueryPlugin_' . $class;
            $vars = get_class_vars($realClass);
            $loop = isset($vars['phpQueryMethods']) && !is_null($vars['phpQueryMethods'])
            ? $vars['phpQueryMethods'] : get_class_methods($realClass);
            foreach ($loop as $method) {
                if ($method == '__initialize') {
                    continue;
                }

                if (!is_callable([$realClass, $method])) {
                    continue;
                }

                if (isset(self::$pluginsStaticMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$c}' conflicts with same method from plugin '" . self::$pluginsStaticMethods[$method] . "'");
                    return;
                }
                self::$pluginsStaticMethods[$method] = $class;
            }
            if (method_exists($realClass, '__initialize')) {
                call_user_func_array([$realClass, '__initialize'], []);
            }

        }
        if (class_exists('phpQueryObjectPlugin_' . $class)) {
            $realClass = 'phpQueryObjectPlugin_' . $class;
            $vars = get_class_vars($realClass);
            $loop = isset($vars['phpQueryMethods']) && !is_null($vars['phpQueryMethods'])
            ? $vars['phpQueryMethods'] : get_class_methods($realClass);
            foreach ($loop as $method) {
                if (!is_callable([$realClass, $method])) {
                    continue;
                }

                if (isset(self::$pluginsMethods[$method])) {
                    throw new Exception("Duplicate method '{$method}' from plugin '{$c}' conflicts with same method from plugin '" . self::$pluginsMethods[$method] . "'");
                    continue;
                }
                self::$pluginsMethods[$method] = $class;
            }
        }
        return true;
    }

    /**
     * Multi-purpose function.
     * Use pq() as shortcut.
     *
     * In below examples, $pq is any result of pq(); function.
     *
     * 1. Import markup into existing document (without any attaching):
     * - Import into selected document:
     *   pq('<div/>')                // DOESNT accept text nodes at beginning of input string !
     * - Import into document with ID from $pq->getDocumentID():
     *   pq('<div/>', $pq->getDocumentID())
     * - Import into same document as DOMNode belongs to:
     *   pq('<div/>', DOMNode)
     * - Import into document from phpQuery object:
     *   pq('<div/>', $pq)
     *
     * 2. Run query:
     * - Run query on last selected document:
     *   pq('div.myClass')
     * - Run query on document with ID from $pq->getDocumentID():
     *   pq('div.myClass', $pq->getDocumentID())
     * - Run query on same document as DOMNode belongs to and use node(s)as root for query:
     *   pq('div.myClass', DOMNode)
     * - Run query on document from phpQuery object
     *   and use object's stack as root node(s) for query:
     *   pq('div.myClass', $pq)
     *
     * phpQuery object or false in case of error.
     * @param  string|DOMNode|DOMNodeList|array                                                                                  $arg1    HTML markup, CSS Selector, DOMNode or array of DOMNodes
     * @param  string|phpQueryObject|DOMNode                                                                                     $context DOM ID from $pq->getDocumentID(), phpQuery object (determines also query root) or DOMNode (determines also query root)
     * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery|QueryTemplatesPhpQuery|false
     */
    public static function pq($arg1, $context = null)
    {
        if ($arg1 instanceof DOMNODE && !isset($context)) {
            foreach (phpQuery::$documents as $documentWrapper) {
                $compare = $arg1 instanceof DOMDocument
                ? $arg1 : $arg1->ownerDocument;
                if ($documentWrapper->document->isSameNode($compare)) {
                    $context = $documentWrapper->id;
                }

            }
        }
        if (!$context) {
            $domId = self::$defaultDocumentID;
            if (!$domId) {
                throw new Exception("Can't use last created DOM, because there isn't any. Use phpQuery::newDocument() first.");
            }
        } else if (is_object($context) && $context instanceof phpQueryObject) {
            $domId = $context->getDocumentID();
        } else if ($context instanceof DOMDOCUMENT) {
            $domId = self::getDocumentID($context);
            if (!$domId) {
                $domId = self::newDocument($context)->getDocumentID();
            }
        } else if ($context instanceof DOMNODE) {
            $domId = self::getDocumentID($context);
            if (!$domId) {
                throw new Exception('Orphaned DOMNode');
            }
        } else {
            $domId = $context;
        }

        if ($arg1 instanceof phpQueryObject) {
            if ($arg1->getDocumentID() == $domId) {
                return $arg1;
            }

            $class = get_class($arg1);
            // support inheritance by passing old object to overloaded constructor
            $phpQuery = $class != 'phpQuery' ? new $class($arg1, $domId) : new phpQueryObject($domId);
            $phpQuery->elements = [];
            foreach ($arg1->elements as $node) {
                $phpQuery->elements[] = $phpQuery->document->importNode($node, true);
            }

            return $phpQuery;
        } else if ($arg1 instanceof DOMNODE || (is_array($arg1) && isset($arg1[0]) && $arg1[0] instanceof DOMNODE)) {
            /*
             * Wrap DOM nodes with phpQuery object, import into document when needed:
             * pq(array($domNode1, $domNode2))
             */
            $phpQuery = new phpQueryObject($domId);
            if (!($arg1 instanceof DOMNODELIST) && !is_array($arg1)) {
                $arg1 = [$arg1];
            }

            $phpQuery->elements = [];
            foreach ($arg1 as $node) {
                $sameDocument = $node->ownerDocument instanceof DOMDOCUMENT
                && !$node->ownerDocument->isSameNode($phpQuery->document);
                $phpQuery->elements[] = $sameDocument ? $phpQuery->document->importNode($node, true) : $node;
            }
            return $phpQuery;
        } else if (self::isMarkup($arg1)) {
            /**
             * Import HTML:
             * pq('<div/>')
             */
            $phpQuery = new phpQueryObject($domId);
            return $phpQuery->newInstance(
                $phpQuery->documentWrapper->import($arg1)
            );
        } else {
            /**
             * Run CSS query:
             * pq('div.myClass')
             */
            $phpQuery = new phpQueryObject($domId);
            if ($context && $context instanceof phpQueryObject) {
                $phpQuery->elements = $context->elements;
            } else if ($context && $context instanceof DOMNODELIST) {
                $phpQuery->elements = [];
                foreach ($context as $node) {
                    $phpQuery->elements[] = $node;
                }

            } else if ($context && $context instanceof DOMNODE) {
                $phpQuery->elements = [$context];
            }

            return $phpQuery->find($arg1);
        }
    }

    public static function removeData($node, $name, $documentID)
    {
        if (!$documentID)
        // TODO check if this works
        {
            $documentID = self::getDocumentID($node);
        }

        $document = phpQuery::$documents[$documentID];
        $node = self::dataSetupNode($node, $documentID);
        $id = $node->dataID;
        if ($name) {
            if (isset($document->data[$id][$name])) {
                unset($document->data[$id][$name]);
            }

            $name = null;
            foreach ($document->data[$id] as $name) {
                break;
            }

            if (!$name) {
                self::removeData($node, $name, $documentID);
            }

        } else {
            self::dataRemoveNode($node, $documentID);
        }
    }

    /**
     * Sets default document to $id. Document has to be loaded prior
     * to using this method.
     * $id can be retrived via getDocumentID() or getDocumentIDRef().
     *
     * @param unknown_type $id
     */
    public static function selectDocument($id)
    {
        $id = self::getDocumentID($id);
        self::debug("Selecting document '$id' as default one");
        self::$defaultDocumentID = self::getDocumentID($id);
    }

    /**
     * Returns JSON representation of $data.
     *
     * @static
     * @param  mixed    $data
     * @return string
     */
    public static function toJSON($data)
    {
        if (function_exists('json_encode')) {
            return json_encode($data);
        }

        require_once 'Zend/Json/Encoder.php';
        return Zend_Json_Encoder::encode($data);
    }

    /**
     * @param $str
     */
    public static function trim($str)
    {
        return trim($str);
    }

    /**
     * @param $array
     */
    public static function unique($array)
    {
        return array_unique($array);
    }

    /**
     * Unloades all or specified document from memory.
     *
     * @param mixed $documentID @see phpQuery::getDocumentID() for supported types.
     */
    public static function unloadDocuments($id = null)
    {
        if (isset($id)) {
            if ($id = self::getDocumentID($id)) {
                unset(phpQuery::$documents[$id]);
            }

        } else {
            foreach (phpQuery::$documents as $k => $v) {
                unset(phpQuery::$documents[$k]);
            }
        }
    }

    /**
     * Parses phpQuery object or HTML result against PHP tags and makes them active.
     *
     * @deprecated
     * @param  phpQuery|string $content
     * @return string
     */
    public static function unsafePHPTags($content)
    {
        return self::markupToPHP($content);
    }

    /**
     * @todo support PHP tags in input
     * @todo support passing DOMDocument object from self::loadDocument
     * @param  unknown_type $html
     * @param  unknown_type $domId
     * @return unknown      New DOM ID
     */
    protected static function createDocumentWrapper($html, $contentType = null, $documentID = null)
    {
        if (function_exists('domxml_open_mem')) {
            throw new Exception("Old PHP4 DOM XML extension detected. phpQuery won't work until this extension is enabled.");
        }

        $document = null;
        if ($html instanceof DOMDOCUMENT) {
            if (self::getDocumentID($html)) {
                // document already exists in phpQuery::$documents, make a copy
                $document = clone $html;
            } else {
                // new document, add it to phpQuery::$documents
                $wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
            }
        } else {
            $wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
        }
        // bind document
        phpQuery::$documents[$wrapper->id] = $wrapper;
        // remember last loaded document
        phpQuery::selectDocument($wrapper->id);
        return $wrapper->id;
    }

    /**
     * @param $node
     * @param $documentID
     */
    protected static function dataRemoveNode($node, $documentID)
    {
        // search are return if alredy exists
        foreach (phpQuery::$documents[$documentID]->dataNodes as $k => $dataNode) {
            if ($node->isSameNode($dataNode)) {
                unset(self::$documents[$documentID]->dataNodes[$k]);
                unset(self::$documents[$documentID]->data[$dataNode->dataID]);
            }
        }
    }

    /**
     * @param  $node
     * @param  $documentID
     * @return mixed
     */
    protected static function dataSetupNode($node, $documentID)
    {
        // search are return if alredy exists
        foreach (phpQuery::$documents[$documentID]->dataNodes as $dataNode) {
            if ($node->isSameNode($dataNode)) {
                return $dataNode;
            }

        }
        // if doesn't, add it
        phpQuery::$documents[$documentID]->dataNodes[] = $node;
        return $node;
    }
}
