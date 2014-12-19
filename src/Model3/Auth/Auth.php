<?php

namespace Model3\Auth;

use Model3\Registry\Registry;

class Auth
{

    protected $_config;

    public function __construct()
    {
        $registry = Registry::getInstance();
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
     *
     * @param string $user
     * @param string $pass
     * @return bool
     */
    public function authenticate($user, $pass)
    {
        $dbs = Registry::getInstance()->get('databases');
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
     *
     * @param string $element
     * @return array
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

    public static function deleteCredentials()
    {
        unset($_SESSION['__M3']['Credentials']);
    }

    public static function isAuth()
    {
        return isset($_SESSION['__M3']['Credentials']);
    }

    public static function refreshCredentials($user, $pass)
    {
        self::deleteCredentials();
        $auth = new Auth();
        return $auth->authenticate($user, $pass);
    }

}