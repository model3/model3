<?php

namespace Model3\Config;

use Model3\Exception\Model3Exception;

class Config
{
	private $_configArray;
	
	public function __construct($filename)
	{
		if (empty($filename)) 
		{
            throw new Model3Exception('Missing filename');
        }
		$this->_configArray = parse_ini_file($filename, true);		
	}
	
	public function getArray()
	{
		return $this->_configArray;
	}
}