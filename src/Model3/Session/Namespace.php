<?php
class Model3_Session_Namespace
{
    protected $_name;

    public function __construct($name = 'Default')
    {
        if(!Model3_Session::sessionExist())
        {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception('Session not found');
        }
        if(!isset($_SESSION['__M3']['Namespaces'][$name]))
            $_SESSION['__M3']['Namespaces'][$name] = array();
        $this->_name = $name;
    }

    /*
	* Metodo magico set
	* @param $property
	* @param $value
	*/
    public function __set($property, $value)
    {
        $_SESSION['__M3']['Namespaces'][$this->_name][$property] = $value;
    }

	/*
	* Metodo magico get
	* @param $property
	*/
    public function __get($property)
    {
        if(isset($_SESSION['__M3']['Namespaces'][$this->_name][$property]))
            return $_SESSION['__M3']['Namespaces'][$this->_name][$property];
        return NULL;
    }
}
