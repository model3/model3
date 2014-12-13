<?php

namespace Model3\Session;

use Model3\Exception\Model3Exception;

class SessionNamespace
{
    protected $_name;

    public function __construct($name = 'Default')
    {
        if(!Session::sessionExist())
        {
            throw new Model3Exception('Session not found');
        }
        if(!isset($_SESSION['__M3']['Namespaces'][$name]))
            $_SESSION['__M3']['Namespaces'][$name] = array();
        $this->_name = $name;
    }

    public function __set($property, $value)
    {
        $_SESSION['__M3']['Namespaces'][$this->_name][$property] = $value;
    }

    public function __get($property)
    {
        if(isset($_SESSION['__M3']['Namespaces'][$this->_name][$property]))
            return $_SESSION['__M3']['Namespaces'][$this->_name][$property];
        return NULL;
    }
}
