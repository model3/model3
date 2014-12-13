<?php
class Model3_Db_Row
{
	protected $_data;
	
	public function __construct()
	{
	}

	public function setData($array)
	{
		$this->_data = $array;
	}
	
	public function getData()
	{
		return $this->_data;
	}
	
	public function __set($key, $value)
	{
		if($this->validateKey($key) == true)
			$this->_data[$key] = $value;
	}
	
	public function __get($key)
	{
		if($this->validateKey($key) == true)
			return $this->_data[$key];
		return false;
	}
	
	protected function validateKey($key)
	{
		if(is_null($this->_data) == false && is_array($this->_data) && array_key_exists($key, $this->_data) == true)
			return true;
		return false;
	}
}
?>