<?php

namespace Model3\Registry;

use Model3\Exception\Model3Exception;

class Registry extends \ArrayObject
{
    private static $_registryClassName = 'Registry';

    /**
     * @var Registry
     */
	private static $_registry = null;
	
	/**
	*
	* @return Registry $_registry
	*/
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }
        return self::$_registry;
    }

    /**
     *
     * @param Registry $registry
     * @throws Model3Exception
     */
	public static function setInstance(Registry $registry)
    {
        if (self::$_registry !== null) {
            throw new Model3Exception('Registry already initialized');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

    /**
     *
     * @param string $registryClassName
     * @throws Model3Exception
     */
    public static function setClassName($registryClassName = 'Registry')
    {
        if (self::$_registry !== null) {
            throw new Model3Exception('Registry already initialized');
        }

        if (!is_string($registryClassName)) {
            throw new Model3Exception("Not a class name");
        }
    }

    public static function _unsetInstance()
    {
        self::$_registry = null;
    }

    /**
     *
     * @param string $index
     * @throws Model3Exception
     * @return mixed $instance->offsetGet($index);
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new Model3Exception("Index '$index' is not defined");
        }

        return $instance->offsetGet($index);
    }

	/**
	* 
	* @param string $index
	* @param string $value
	*/
    public static function set($index, $value)
    {
        $instance = self::getInstance();

        $instance->offsetSet($index, $value);
    }

	/**
	*
	* @param string $index
	* @return self::$_registry->offsetExists($index)
	*/
    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

	/**
	*
	* @param string $index
	* @return bool
	*/
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }
}