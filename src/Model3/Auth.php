<?php

namespace Model3;

/**
 * Clase Auth, esta clase auntentifica al usuario en el sistema.
 * @package Model3
 * @author Hector Benitez
 * @version 0.3
 * @copyright 2010 Hector Benitez
 */
class Model3_Auth
{

    protected $_config;

    public function __construct()
    {
        $registry = Model3_Registry::getInstance();
        $config = $registry->get('config');
        $this->_config = $config->getArray();
        $this->_config = $this->_config['user_data'];
    }

    public function setTableData($cnx, $table, $usr = 'username', $psw = 'password')
    {
        unset($this->_config);
        $this->_config = array('cnx' => $cnx, 'table' => $table, 'user' => $usr, 'pass' => $psw);
    }

    /**
     * Autentifica al usuario en el sistema por medio de su username y password
     * @param string $user El username del usuario
     * @param string $pass El password del usuario
     * @return bool|Regresa true si los datos del usuario son validos en la BD, en caso de fallar regresa false
     */
    public function authenticate($user, $pass)
    {
        $dbs = Model3_Registry::getInstance()->get('databases');
        $em = $dbs[$this->_config['cnx']];
        /* @var $em Doctrine\ORM\EntityManager */
        
        $user = $em->getRepository($this->_config['table'])->findOneBy(array($this->_config['user'] => $user));
        if ($user)
        {
            $method = 'get'.ucwords($this->_config['pass']);
            if ($user->$method() == $pass)
            {
                $_SESSION['__M3']['Credentials'] = $user->getData();
                return true;
            }
            return false;
        }
    }

    /**
     * Obtiene la informacion del usuario auntentificado en el sistema
     * @param string $element la informacion especifica del usuario
     * @return $_SESSION['__M3']['Credentials'] o $_SESSION['__M3']['Credentials'][$element] | Si element es null regresa
     * toda la informacion del usuario  en caso contrario la que se le especifico en $element
     */
    public static function getCredentials($element = null)
    {
        if (!isset($_SESSION['__M3']))
            return null;
        if (!isset($_SESSION['__M3']['Credentials']))
            return null;
        if ($element == null)
            return $_SESSION['__M3']['Credentials'];
        else
            return $_SESSION['__M3']['Credentials'][$element];
    }

    /**
     * Borra toda la informacion del usuario en las variables de sesion
     */
    public static function deleteCredentials()
    {
        unset($_SESSION['__M3']['Credentials']);
    }

    /**
     * Verifica si el usuario esta auntentificado en el sistema
     */
    public static function isAuth()
    {
        return isset($_SESSION['__M3']['Credentials']);
    }

    public static function refreshCredentials($user, $pass)
    {
        self::deleteCredentials();
        $auth = new Model3_Auth();
        return $auth->authenticate($user, $pass);
    }

}