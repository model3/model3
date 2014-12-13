<?php

namespace Model3\Validator;


class Element
{
	private $_inputName;
	private $_validatorType;
	private $_parameters;
	private $_errorString;	
	
	public function __construct($input, $type, $parameters, $error)
	{
		$this->_inputName = $input;
		$this->_validatorType = $type;
		$this->_parameters = $parameters;
		$this->_errorString = $error;
	}
	
	public function getName()
	{
		return $this->_inputName;
	}
	
	public function getType()
	{
		return $this->_validatorType;
	}
	
	public function getParameters()
	{
		return $this->_parameters;
	}
	
	public function getErrorString()
	{
		return $this->_errorString;
	}
}