<?php

namespace Model3\Acl;

class Acl
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

    public function allowGroups()
    {
        $this->_aGroups = func_get_args();
    }

    public function allowUsers()
    {
        $this->_aUsers = func_get_args();
    }

    public function denyGroups()
    {
        $this->_dGroups = func_get_args();
    }

    public function denyUsers()
    {
        $this->_dUsers = func_get_args();
    }

    static public function setPermissionMode($permissionMode)
    {
        self::$_permissionMode = $permissionMode;
    }

    static public function getPermissionMode()
    {
        return self::$_permissionMode;
    }

    /**
     *
     * @param $user
     * @param $group
     * @return bool|resource
     */
    public function isAllowed($user, $group)
    {
        $authorization = Acl::getPermissionMode();

        if (!in_array($user, $this->_dUsers))
        {
            if (in_array($user, $this->_aUsers))
                $authorization = true;
            else
            {
                if (!in_array($group, $this->_dGroups))
                    if (in_array($group, $this->_aGroups))
                        $authorization = true;
            }
        }
        return $authorization;
    }

}