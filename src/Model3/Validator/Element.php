<?php

/**
* Clase Validator_Element del M3
*
* 
* @author Hector Benitez
* @version 1.0
* @copyright 2009 Hector Benitez
*/

class Model3_Validator_Element
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
	
   /*
	* Obtiene el nombre del elemento a validar
	* @return $this->_inputName
	*/
	public function getName()
	{
		return $this->_inputName;
	}
	
   /*
	* Obtiene el tipo del elemento a validar
	* @return $this->_validatorType
	*/
	public function getType()
	{
		return $this->_validatorType;
	}
	
   /*
	* Obtiene los parametros a validar
	* @return $this->_parameters
	*/
	public function getParameters()
	{
		return $this->_parameters;
	}
	
   /*
	* Obtiene los errores de validacion
	* @return $this->_errorString
	*/
	public function getErrorString()
	{
		return $this->_errorString;
	}
}