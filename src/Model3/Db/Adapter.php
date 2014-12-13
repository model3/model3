<?php
/**
* Capa de abstracta de datos
*
* Esta clase especifica una capa de datos abstracta basada en MySQL Server 5.x
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyright 2008 Hector Benitez
*/
abstract class Model3_Db_Adapter
{
	protected static $_defaultDb;
	protected $_db;
	protected $_error;

	public function __construct($db = null)
	{
		if($db == null)
			if(self::$_defaultDb == null)
			{
            	require_once 'Model3/Exception.php';
            	throw new Model3_Exception('No se encuentra la Base de Datos');
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
    * Obtiene el error en un string de la consulta ejecutada en la BD
	* @return $this->_db->errorStr()| Regresa el error de la consulta ejecutada en la BD en caso de fallo
    */
	public function getErrorStr()
	{
		return $this->_db->errorStr();
	}
	
	/**
    * Obtiene el error de la consulta ejecutada en la BD
	* @return $this->_db->_error()| Regresa el error de la consulta ejecutada en la BD en caso de fallo
    */
	public function getError()
	{
		return $this->_error;
	}
	
	/**
    * Obtiene el error de la consulta ejecutada en la BD
	* @return $this->_db->_error()| Regresa el error de la consulta ejecutada en la BD en caso de fallo
    */
	public static final function setDefaultAdapter($db)
    {
        self::$_defaultDb = $db;
    }
	
	/**
    * Para evitar el SQLInjection
	* @param $cadena la cadena para hacer los escapes de caracteres
	* @return $this->_db->escape($cadena)| Regresa la cadena correcta para mysql y evitar SQLInjection
    */
	public function escape($cadena)
	{
		return $this->_db->escape($cadena);
	}
	
	/**
    * Obtiene el ultimo id insertado en la bd
	* @return $this->_db->insertId() | Regresa el ultimo id insertado en la bd
    */
	public function insertId()
	{
		return $this->_db->insertId();
	}
	
	 /**
         * El parametro data va por referencia
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