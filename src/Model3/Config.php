<?php
class Model3_Config
{
	private $_configArray;
	
	public function __construct($filename)
	{
		if (empty($filename)) 
		{
            require_once 'Model3/Exception.php';
            throw new Model3_Exception('No se especifico un nombre de archivo');
        }
		$this->_configArray = parse_ini_file($filename, true);		
	}
	
	public function getArray()
	{
		return $this->_configArray;
	}
}