<?php

namespace Model3\Db;

use Model3\Exception\Model3Exception;

abstract class Adapter
{
	protected static $_defaultDb;

	/**
	 * @var Db
	 */
	protected $_db;
	protected $_error;

	public function __construct($db = null)
	{
		if($db == null)
			if(self::$_defaultDb == null)
			{
            	throw new Model3Exception('Database not found');
        	}
			else
				$this->_db = self::$_defaultDb;
		else
			$this->_db = $db;
		$this->_db->connect();
	}

    public function setDb($db)
    {
        $this->_db = $db;
        $this->_db->connect();
    }
	/**
    *
	* @return $this->_db->errorStr()
    */
	public function getErrorStr()
	{
		return $this->_db->errorStr();
	}
	
	/**
    *
	* @return $this->_db->_error()
    */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 *
	 * @param $db
	 * @return $this ->_db->_error()
	 */
	public static final function setDefaultAdapter($db)
    {
        self::$_defaultDb = $db;
    }
	
	/**
    *
	* @param $string
	* @return $this->_db->escape($string)
    */
	public function escape($string)
	{
		return $this->_db->escape($string);
	}
	
	/**
    *
	* @return $this->_db->insertId()
    */
	public function insertId()
	{
		return $this->_db->insertId();
	}
	
	 /**
         *
         * @param array $data
         * @return bool
         */
	public function escapeArray(&$data)
	{
        if(is_array($data) == false)
            return false;
        foreach($data as $key => $d)
        {
            if(is_array($data[$key]))
                $this->escapeArray($data[$key]);
            else
                $data[$key] = $this->_db->escape($d);
        }
        return true;
	}
}