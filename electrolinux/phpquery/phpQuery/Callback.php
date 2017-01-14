<?php

/**
 * Callback class introduces currying-like pattern.
 *
 * Example:
 * function foo($param1, $param2, $param3) {
 *   var_dump($param1, $param2, $param3);
 * }
 * $fooCurried = new Callback('foo',
 *   'param1 is now statically set',
 *   new CallbackParam, new CallbackParam
 * );
 * phpQuery::callbackRun($fooCurried,
 *     array('param2 value', 'param3 value'
 * );
 *
 * Callback class is supported in all phpQuery methods which accepts callbacks.
 *
 * @TODO??? return fake forwarding function created via create_function
 * @TODO honor paramStructure
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 *
 * @link http://code.google.com/p/phpquery/wiki/Callbacks#Param_Structures
 */

interface ICallbackNamed
{
    public function getName();

    function hasName();
}

class Callback implements ICallbackNamed
{
    /**
     * @var mixed
     */
    public $callback = null;

    /**
     * @var mixed
     */
    public $params = null;

    /**
     * @var mixed
     */
    protected $name;

    /**
     * @param $callback
     * @param $param1
     * @param null        $param2
     * @param null        $param3
     */
    public function __construct($callback, $param1 = null, $param2 = null,
        $param3 = null) {
        $params = func_get_args();
        $params = array_slice($params, 1);
        if ($callback instanceof Callback) {
            // TODO implement recurention
        } else {
            $this->callback = $callback;
            $this->params = $params;
        }
    }

    public function getName()
    {
        return 'Callback: ' . $this->name;
    }

    public function hasName()
    {
        return isset($this->name) && $this->name;
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}

/**
 * Shorthand for new Callback(create_function(...), ...);
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackBody extends Callback
{
    /**
     * @param $paramList
     * @param $code
     * @param $param1
     * @param null         $param2
     * @param null         $param3
     */
    public function __construct($paramList, $code, $param1 = null, $param2 = null,
        $param3 = null) {
        $params = func_get_args();
        $params = array_slice($params, 2);
        $this->callback = create_function($paramList, $code);
        $this->params = $params;
    }
}

/**
 * Callback type which on execution returns reference passed during creation.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackReturnReference extends Callback implements ICallbackNamed
{
    /**
     * @var mixed
     */
    protected $reference;

    /**
     * @param $reference
     * @param $name
     */
    public function __construct(&$reference, $name = null)
    {
        $this->reference = &$reference;
        $this->callback = [$this, 'callback'];
    }

    /**
     * @return mixed
     */
    public function callback()
    {
        return $this->reference;
    }

    public function getName()
    {
        return 'Callback: ' . $this->name;
    }

    public function hasName()
    {
        return isset($this->name) && $this->name;
    }
}

/**
 * Callback type which on execution returns value passed during creation.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackReturnValue extends Callback implements ICallbackNamed
{
    /**
     * @var mixed
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param $value
     * @param $name
     */
    public function __construct($value, $name = null)
    {
        $this->value = &$value;
        $this->name = $name;
        $this->callback = [$this, 'callback'];
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function callback()
    {
        return $this->value;
    }

    public function getName()
    {
        return 'Callback: ' . $this->name;
    }

    public function hasName()
    {
        return isset($this->name) && $this->name;
    }
}

/**
 * CallbackParameterToReference can be used when we don't really want a callback,
 * only parameter passed to it. CallbackParameterToReference takes first
 * parameter's value and passes it to reference.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackParameterToReference extends Callback
{
    /**
     * @TODO implement $paramIndex;
     * param index choose which callback param will be passed to reference
     * @param $reference
     */
    public function __construct(&$reference)
    {
        $this->callback = &$reference;
    }
}

class CallbackParam
{
}
