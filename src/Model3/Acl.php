<?php

/**
 * Clase Acl, esta clase controlara el acceso al sistema.
 * @package Model3
 * @author Hector Benitez
 * @version 0.3
 * @copyright 2008 Hector Benitez
 */
class Model3_Acl
{

    /**
     *
     * @var array
     */
    protected $_aUsers;
    protected $_aGroups;
    protected $_dUsers;
    protected $_dGroups;
    static protected $_permissionMode;

    public function __construct()
    {
        $this->_aUsers = array();
        $this->_aGroups = array();
        $this->_dUsers = array();
        $this->_dGroups = array();
        $this->setPermissionMode(false);
    }

    /**
     * Configura los permisos de acceso a grupos de usuarios
     * @param mixed $group,... OPTIONAL Cualquier cantidad de grupos para autorizar
     */
    public function allowGroups()
    {
        $this->_aGroups = func_get_args();
    }

    /**
     * Configura los permisos de acceso al sistema a usuarios
     */
    public function allowUsers()
    {
        $this->_aUsers = func_get_args();
    }

    /**
     * Configura los permisos de acceso al sistema a los grupos de usuarios
     */
    public function denyGroups()
    {
        $this->_dGroups = func_get_args();
    }

    /**
     * Configura los permisos de acceso al sistema a los grupos de usuarios
     */
    public function denyUsers()
    {
        $this->_dUsers = func_get_args();
    }

    /**
     * Configura el modo por default de acceso al sistema para los grupos de usuarios y usuarios
     */
    static public function setPermissionMode($permissionMode)
    {
        self::$_permissionMode = $permissionMode;
    }

    /**
     * Obtiene el modo por default de acceso al sistema para los grupos de usuarios y usuarios
     */
    static public function getPermissionMode()
    {
        return self::$_permissionMode;
    }

    /**
     * Configura los permisos de acceso al sistema a los grupos de usuarios
     * @return bool|resource Regresa true si el usuario tiene acceso , en caso de fallar regresa false
     */
    public function isAllowed($user, $group)
    {
        $autorizacion = Model3_Acl::getPermissionMode();

        if (!in_array($user, $this->_dUsers))
        {
            if (in_array($user, $this->_aUsers))
                $autorizacion = true;
            else
            {
                if (!in_array($group, $this->_dGroups))
                    if (in_array($group, $this->_aGroups))
                        $autorizacion = true;
            }
        }
        return $autorizacion;
    }

}