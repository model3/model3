<?php
/*
 * Created on 07/10/2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
define('ACL_DB_ALLOW', 1);
define('ACL_DB_DENY', 2);
define('ACL_DB_UNDEFINED', 3);

class Model3_Acl_Db extends Model3_Acl
{
	protected $_usersGroupsTable;
	protected $_aclPermissionsTable;
	protected $_resourcesTable;
	protected $_actualResource;
	
	public function __construct()
	{
		parent::__construct();
		$this->setUsersGroupsTable('m3_users_groups');
		$this->setAclPermissionsTable('m3_acl_permissions');
		$this->setResourcesTable('m3_resources');
		$this->_actualResource = null;
	}

	public function setUsersGroupsTable($usersGroupsTable)
	{
		unset($this->_usersGroupsTable);
		$this->_usersGroupsTable = new Model3_Db_Table($usersGroupsTable);
		return $this;
	}
	
	public function getUsersGroupsTable()
	{
		return $this->_usersGroupsTable;
	}
	
	public function setAclPermissionsTable($aclPermissionsTable)
	{
		unset($this->_aclPermissionsTable);
		$this->_aclPermissionsTable = new Model3_Db_Table($aclPermissionsTable);
		return $this;
	}
	
	public function getAclPermissionsTable()
	{
		return $this->_aclPermissionsTable;
	}
	
	public function setResourcesTable($resourcesTable)
	{
		unset($this->_resourcesTable);
		$this->_resourcesTable = new Model3_Db_Table($resourcesTable);
		return $this;
	}
	
	public function getResourcesTable()
	{
		return $this->_resourcesTable;
	}
	
	public function setActualResource($resourceName)
	{
		$result = $this->_resourcesTable->find('name', array($resourceName));
		$this->_actualResource = is_array($result) && count($result) > 0 ? $result[0]->id : null;
		return $this;
	}
	
	public function getActualResource()
	{
		return $this->_actualResource;
	}

	public function isAllowed($user, $group)
	{
		$finalResult = self::getPermissionMode();
		if($this->_actualResource == null)
		{
			return false;
		}
		$result = $this->verifyUserToResourcePermission($user);
		if($result === ACL_DB_UNDEFINED)
		{
            $group = $this->_usersGroupsTable->find('id', array($group));
            while(is_array($group) && count($group) > 0 && $result === ACL_DB_UNDEFINED)
            {
                $result = $this->verifyGroupToResourcePermission($group[0]->id);
                if($result === ACL_DB_UNDEFINED)
                {
                    $group = $this->_usersGroupsTable->find('id', array($group[0]->id_parent));
                }
                else
                {
                    $finalResult = $result;
                }
            }
        }
        else
            $finalResult = $result;
		return $finalResult;
	}

    protected function verifyUserToResourcePermission($user)
    {
        $finalResult = false;
        $result = $this->_aclPermissionsTable->findWhere('id_user = '.$user.' AND id_resource = '.$this->_actualResource.' AND permission_type = '.ACL_DB_DENY);
        if(is_array($result) == true && count($result) == 0)
        {
            $result = $this->_aclPermissionsTable->findWhere('id_user = '.$user.' AND id_resource = '.$this->_actualResource.' AND permission_type = '.ACL_DB_ALLOW);
            if(is_array($result) == true && count($result) > 0)
            {
                $finalResult = true;
            }
            else
            {
                $finalResult = ACL_DB_UNDEFINED;
            }
        }
        return $finalResult;
    }

    protected function verifyGroupToResourcePermission($group)
    {
        $finalResult = false;
        $result = $this->_aclPermissionsTable->findWhere('id_group = '.$group.' AND id_resource = '.$this->_actualResource.' AND permission_type = '.ACL_DB_DENY);

        if(is_array($result) == true && count($result) == 0)
        {
            $result = $this->_aclPermissionsTable->findWhere('id_group = '.$group.' AND id_resource = '.$this->_actualResource.' AND permission_type = '.ACL_DB_ALLOW);
            if(is_array($result) == true && count($result) > 0)
            {
                $finalResult = true;
            }
            else
            {
                $finalResult = ACL_DB_UNDEFINED;
            }
        }
        return $finalResult;
    }
}