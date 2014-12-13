<?php

namespace Model3\Validator;

class Form
{
	const REQUIRED = 1;
	const MAXLENGTH = 2;
	const MINLENGTH = 3;
	const ALNUM = 4;
	const ALNUMS = 5;
	const NUMERIC = 6;
	const ALPHA = 7;
	const ALPHAS = 8;
	const EMAIL = 9;
	const REGEXP = 10;
	const MINVALUE = 11;
	const MAXVALUE = 12;
	const EQUALTO = 13;
	
	private $_validators;
    private $_errors;
	private $_defaultErrors;
	
	function __construct()
	{
		$this->_validators = array();
        $this->_errors = array();
		
		$this->_defaultErrors = array();
		$this->_defaultErrors[self::REQUIRED] = 'El campo %s es obligatorio';
		$this->_defaultErrors[self::MAXLENGTH] = 'El campo %s acepta %d caracteres maximo';
		$this->_defaultErrors[self::MINLENGTH] = 'El campo %s acepta %d caracteres minimo';
		$this->_defaultErrors[self::ALNUM] = 'El campo %s solo acepta letras y numeros';
		$this->_defaultErrors[self::ALNUMS] = 'El campo %s solo acepta letras, numeros y espacios';
		$this->_defaultErrors[self::NUMERIC] = 'El campo %s solo acepta numeros';
		$this->_defaultErrors[self::ALPHA] = 'El campo %s solo acepta letras';
		$this->_defaultErrors[self::ALPHAS] = 'El campo %s solo acepta letras y espacios';
		$this->_defaultErrors[self::EMAIL] = 'El campo %s solo acepta emails';
		$this->_defaultErrors[self::REGEXP] = 'El campo %s no es valido';
		$this->_defaultErrors[self::MINVALUE] = 'El campo %s acepta minimo el valor %d';
		$this->_defaultErrors[self::MAXVALUE] = 'El campo %s acepta maximo el valor %d';
		$this->_defaultErrors[self::EQUALTO] = 'Los campos %s y %s deben coincidir';
	}
	
	function addValidation($variable, $type, $parameters = NULL, $error = '')
	{
		$validatorInput = new Element($variable, $type, $parameters, $error);
		array_push($this->_validators, $validatorInput);
	}
	
    function getErrors()
    {
        return $this->_errors;
    }

    function validateForm()
    {
        $valid = true;
        $errorString = "";
        $errorDisplay = "";

        if(strcmp($_SERVER['REQUEST_METHOD'],'POST')==0)
        {
                $formVariables = $_POST;
        }
        else
        {
                $formVariables = $_GET;
        }

        foreach($this->_validators as $validator)
        {
                if(array_key_exists($validator->getName(), $this->_errors))
                        continue;
                if(!$this->validateObject($validator, $formVariables, $errorString))
                {
                        $valid = false;
        $this->_errors[$validator->getName()] = $errorString;
                }
        }
        return $valid;
    }

    function validateObject($validator, $formVariables, &$errorString)
    {
        $valid = true;
        $command = $validator->getType();
        $defaultMsg = '';

        if(isset($formVariables[$validator->getName()]))
        {
                $inputValue = $formVariables[$validator->getName()];
        }
        else
        {
                throw new Exception('El campo '.$validator->getName().' no existe en la forma...');
        }

        $valid = $this->validateCommand($command, $validator->getParameters(), $inputValue, $defaultMsg, $validator->getName(), $formVariables);

        if(false == $valid)
        {
            $errorStr = $validator->getErrorString();
            if(!empty($errorStr))
            {
                    $errorString = $validator->getErrorString();
            }
            else
            {
                    $errorString = $defaultMsg;
            }

        }
        return $valid;
    }
	
    function validateCommand($command, $parameters, $inputValue, &$defaultMsg, $variableName,$formVariables)
    {
        $valid = true;
        switch($command)
        {
                case self::REQUIRED:
                        $valid = $this->validRequired($inputValue, $defaultMsg, $variableName);
                        break;
                case self::MAXLENGTH:
                        if(isset($inputValue))
                        {
                                $maxLen = intval($parameters);
                                $valid = $this->validMaxLen($inputValue, $maxLen, $variableName, $defaultMsg);
                        }
                        break;
                case self::MINLENGTH:
                        if(isset($inputValue))
                        {
                                $minLen = intval($parameters);
                                $valid = $this->validMinLen($inputValue, $minLen, $variableName, $defaultMsg);
                        }
                        break;
                case self::ALNUM:
                        if(isset($inputValue))
                        {
                                $valid = $this->testDatatype($inputValue,"/[^A-Za-z0-9]/");
                                if(false == $valid)
                                {
                                        $defaultMsg = sprintf($this->_defaultErrors[self::ALNUM], $variableName);
                                }
                        }
                        break;
                case self::ALNUMS:
                        if(isset($inputValue))
                        {
                                $valid = $this->testDatatype($inputValue,"/[^A-Za-z0-9 ]/");
                                if(false == $valid)
                                {
                                        $defaultMsg = sprintf($this->_defaultErrors[self::ALNUMS], $variableName);
                                }
                        }
                        break;
                case self::NUMERIC:
                        if(isset($inputValue))
                        {
                                $valid = $this->testDatatype($inputValue,"/[^0-9]/");
                                if(false == $valid)
                                {
                                        $defaultMsg = sprintf($this->_defaultErrors[self::NUMERIC], $variableName);
                                }
                        }
                        break;
                case self::ALPHA:
                        if(isset($inputValue))
                        {
                                $valid = $this->testDatatype($inputValue,"/[^A-Za-z]/");
                                if(false == $valid)
                                {
                                        $defaultMsg = sprintf($this->_defaultErrors[self::ALPHA], $variableName);
                                }
                        }
                        break;
                case self::ALPHAS:
                        if(isset($inputValue))
                        {
                                $valid = $this->testDatatype($inputValue,"/[^A-Za-z ]/");
                                if(false == $valid)
                                {
                                        $defaultMsg = sprintf($this->_defaultErrors[self::ALPHAS], $variableName);
                                }
                        }
                        break;
                case self::EMAIL:
                        if(isset($inputValue))
                        {
                                $valid = $this->validateEmail($inputValue);
                                if(false == $valid)
                                {
                                        $defaultMsg = $this->_defaultErrors[self::EMAIL];
                                }				
                        }
                        break;
                case self::REGEXP:
                        if(isset($inputValue))
                        {
                                if(!preg_match($parameters, $inputValue))
                                {
                                        $valid = false;
                                        $defaultMsg = sprintf($this->_defaultErrors[self::REGEXP], $variableName);
                                }
                        }
                        break;
                case self::MAXVALUE:
                        if(isset($inputValue))
                        {
                                $maxVal = intval($parameters);
                                $input = intval($inputValue);
                                if($input > $maxVal)
                                {
                                        $valid = false;
                                        $defaultMsg = sprintf($this->_defaultErrors[self::MAXVALUE], $variableName, $maxVal);
                                }
                        }
                        break;
                case self::MINVALUE:
                        if(isset($inputValue))
                        {
                                $minVal = intval($parameters);
                                $input = intval($inputValue);
                                if($input < $minVal)
                                {
                                        $valid = false;
                                        $defaultMsg = sprintf($this->_defaultErrors[self::MINVALUE], $variableName, $minVal);
                                }
                        }
                        break;
                case self::EQUALTO:
                        if(isset($inputValue))
                        {
                                $value2 = $formVariables[$parameters];
                                $valid = $this->validEqualTo($inputValue, $value2, $variableName, $parameters, $defaultMsg);
                        }
                        break;
        }
        return $valid;
    }

	function validRequired($inputValue, &$defaultMsg, $variableName)
	{
		$valid = true;
      	if(!isset($inputValue) || strlen($inputValue) <= 0)
		{
			$valid = false;
			$defaultMsg = sprintf($this->_defaultErrors[self::REQUIRED], $variableName);
		}
	  	return $valid;
	}

	function validMaxLen($inputValue, $maxLen, $variableName, &$defaultMsg)
	{
		$valid = true;
		$inputLength = strlen($inputValue);
		if($inputLength > $maxLen)
		{
			$valid = false;
			$defaultMsg = sprintf($this->_defaultErrors[self::MAXLENGTH], $variableName, $maxLen);
		}
		return $valid;
	}

	function validMinLen($inputValue, $minLen, $variableName, &$defaultMsg)
	{
		$valid = true;
		$inputLength = strlen($inputValue);
		if($inputLength < $minLen)
		{
			$valid = false;
			$defaultMsg = sprintf($this->_defaultErrors[self::MINLENGTH], $variableName, $minLen);
		}	
		return $valid;
	}

	function testDatatype($inputValue, $regExp)
	{
            if(preg_match($regExp, $inputValue))
            {
                return false;
            }
            return true;
	}

	function validateEmail($email) 
	{
		return preg_match("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$^", $email);
	}

	function validEqualTo($value1, $value2, $name1, $name2, &$defaultMsg)
	{
		if($value1 === $value2)
			return true;
		$defaultMsg = sprintf($this->_defaultErrors[self::EQUALTO], $name1, $name2);
		return false;
	}
}