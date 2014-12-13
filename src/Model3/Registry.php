<?php
/**
* Clase Registry del M3
*
* Esta clase inicializa el registro del modelo
* @author Hector Benitez
* @version 1.0
* @copyright 2009 Hector Benitez
*/
class Model3_Registry extends ArrayObject
{
    private static $_registryClassName = 'Model3_Registry';
	private static $_registry = null;
	
	/**
	* Obtiene las instancias del registro
	* @return Model3_Registry $_registry
	*/
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }
        return self::$_registry;
    }

    /**
	* Coloca las instancias en el registro
	* @param $registry
	*/
	public static function setInstance(Model3_Registry $registry)
    {
        if (self::$_registry !== null) {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception('Registry ya esta inicializado');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    /**
	* Inicializa el registro
	*/
    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

	/**
	* Coloca el nombre de la clase del registro
	* @param string $registryClassName Nombre de la clase del registro
	*/
    public static function setClassName($registryClassName = 'Model3_Registry')
    {
        if (self::$_registry !== null) {
            require_once 'Model3/Exception.php';
            throw new Exception('Registry ya esta inicializado');
        }

        if (!is_string($registryClassName)) {
            require_once 'Model3/Exception.php';
            throw new Exception("El argument no es un nombre de clase");
        }

        require_once 'Model3/Loader.php';
        Model3_Loader::loadClass($registryClassName);
        self::$_registryClassName = $registryClassName;
    }

	/**
	* Libera la instancia
	*/
    public static function _unsetInstance()
    {
        self::$_registry = null;
    }

	/**
	* 
	* @param string $index
	* @return $instance->offsetGet($index);
	*/
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception("El indice '$index' no esta definido");
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
	* Verifica si esta registrado
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
	* Verifica si esta offset existe
	* @param string $index
	* @return array_key_exists($index, $this)
	*/
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }
}