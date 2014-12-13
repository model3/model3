<?php

namespace Model3\Db;

/**
* Capa de datos, para el proyecto multinivel "VOIP Life"
*
* Esta clase especifica una capa de datos basada en MySQL Server 5.x
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyright 2008 Hector Benitez
*/

/**
* FETCH_ASSOC - La fila es un arreglo asociativo
*/
define("FETCH_ASSOC",1);
/**
* FETCH_ROW - La fila es un arreglo numerico
*/
define("FETCH_ROW",2);
/**
* FETCH_BOTH - La fila es un arreglo asociativo y numerico
*/
define("FETCH_BOTH",3);
/**
* FETCH_OBJECT - La fila es un objeto
*/
define("FETCH_OBJECT",4);

class Db
{
	private $_db;
	private $_server;
	private $_user;
	private $_pass;

	/**
	 * @var bool|resource
	 */
	private $_cnx = false;
	private $_results = array();
	private $_last = null;
	
	/**
    * Control de errores
    */
	private $_errno = 0;
	private $_error = '';

	/**
	 *
	 * @param $config
	 * @internal param string $host
	 * @internal param string $user
	 * @internal param string $pass
	 */
	public function __construct($config)
	{
		$this->_server = $config['host'];
		$this->_user = $config['user'];
		$this->_pass = $config['pass']; 
		$this->_db = $config['name']; 
	}
	
	/**
    *
	* @return bool|resource
    */
	public function connect()
	{
		if($this->_cnx)
			return $this->_cnx;
		$this->_cnx = mysql_connect($this->_server,$this->_user,$this->_pass);
		if(!$this->_cnx)
		{
			$this->_errno = mysql_errno();
			$this->_error = mysql_error();
			return false;
		}		
		if($this->_db)
			mysql_select_db($this->_db);			
		return $this->_cnx;
	}
	
	/**
    *
	* @return bool
    */
	public function close()
	{
		if($this->_cnx)
			if(!mysql_close($this->_cnx))
			{
				$this->_errno = mysql_errno();
				$this->_error = mysql_error();
				return false;
			}
			else
			{
				$this->_cnx = false;
				return true;
			}
		$this->_errno = 0;
		$this->_error = 'No open cnx';
		return false;
	}
	
	/**
    *
	* @return bool
    */
	public function isOpen()
	{
		if($this->_cnx)
			return true;
		else
			return false;
	}
	
	/**
    *
	* @param string $sql
	* @return bool|int
    */
	public function execute($sql)
	{
		if(!$this->isOpen())
		{
			$this->_errno = 0;
			$this->_error = 'No cnx';
			return false;
		}
		$parts = preg_split('/ /',trim($sql));
		$type = strtolower($parts[0]);
		
		$type = str_replace('(', '', $type);
		
		$hash = md5($sql);
		$this->_last = $hash;
		
		if($type == 'select' || $type == 'describe')
		{
			$res = mysql_query($sql, $this->_cnx);
			if($res)
			{
				if(isset($this->_results[$hash]))
					mysql_free_result($this->_results[$hash]);
				$this->_results[$hash] = $res;
				return $hash;
			}
			else
			{
				$this->_errno = mysql_errno();
				$this->_error = mysql_error();
				return false;
			}			
		}
		else
		{
			$res = mysql_query($sql, $this->_cnx);
			if($res)
				return $res;
			$this->_errno = mysql_errno();
			$this->_error = mysql_error();
			return false;
		}
	}
	
	/**
    *
	* @param int $res
	* @return int
    */
	public function count($res = null)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return 0;		
		$count = mysql_num_rows($this->_results[$res]);
		if(!$count)
			$count = 0;
		return $count;
	}
	
	/**
    *
	* @param string $sql
	* @return string
    */
	public function escape($sql)
	{
		if (function_exists('mysql_real_escape_string'))
		{
			return mysql_real_escape_string($sql, $this->_cnx);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			return mysql_escape_string($sql);
		}
		else
		{
			return addslashes($sql);
		}
	}
	
	/**
    *
	* @return bool|int
    */
	public function affectedRows()
	{
		if(!$this->isOpen())
			return false;
		return mysql_affected_rows($this->_cnx);
	}
	
	/**
    *
	* @return bool|int
    */
	public function insertId()
	{
		if(!$this->isOpen())
			return false;
		return mysql_insert_id($this->_cnx);
	}
	
	/**
    *
	* @param int $res
	* @param int $fetchmode
	* @return mixed|bool
	* @see FETCH_ASSOC, FETCH_ROW, FETCH_OBJECT, FETCH_BOTH
    */
	public function getRow($res = null, $fetchmode = FETCH_ASSOC)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return false;
		if (FETCH_ASSOC == $fetchmode)
			$row = mysql_fetch_assoc($this->_results[$res]);
		elseif (FETCH_ROW == $fetchmode)
			$row = mysql_fetch_row($this->_results[$res]);
		elseif (FETCH_OBJECT == $fetchmode)
			$row = mysql_fetch_object($this->_results[$res]);
		else
			$row = mysql_fetch_array($this->_results[$res],MYSQL_BOTH);
		return $row;
	}
	
	/**
    *
	* @param int $res
	* @param int $offset
	* @param int $fetchmode
	* @return mixed|bool
	* @see getRow
    */
	public function getRowAt($res = null, $offset = null, $fetchmode = FETCH_ASSOC)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return false;
		if(!empty($offset))
		{
			mysql_data_seek($this->_results[$res], $offset);
		}
		return $this->getRow($res, $fetchmode);
	}
	
	/**
    *
	* @param int $res
	* @return bool
    */
	public function rewind($res = null)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return false;
		mysql_data_seek($this->_results[$res], 0);
		return true;
	}
	
	/**
    *
	* @param int $res
	* @param int $start
	* @param int $count
	* @param int $fetchmode
	* @return array|bool
	* @see getRow
    */
	public function getRows($res = null, $start = 0, $count = 1, $fetchmode = FETCH_ASSOC)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return false;
		mysql_data_seek($this->_results[$res], $start);
		$rows = array();
		for($i=$start; $i<($start+$count); $i++)
		{
			$rows[] = $this->getRow($res, $fetchmode);
		}
		return $rows;
	}
	
	public function getAllRows($res = null, $fetchmode = FETCH_ASSOC)
	{
		if($res == null)
			$res = $this->_last;
		if(!is_resource($this->_results[$res]))
			return false;
		if($this->count() > 0)
			mysql_data_seek($this->_results[$res], 0);
		$rows = array();
		while($rw = $this->getRow($res, $fetchmode))
			$rows[] = $rw;
		return $rows;
	}
	
	/**
    *
	* @return string
    */
	public function errorStr()
	{
		return 'Error No: '.mysql_errno().' Msg: '.mysql_error();;
	}
	
	function __destruct()
	{
		foreach ($this->_results as $result)
		{
			@mysql_free_result($result);
		}
		
		if($this->_cnx)
			if(is_resource($this->_cnx))
				mysql_close($this->_cnx);
	}

	public function isTableEmpty( $table )
	{
		$query = 'SELECT * FROM '.$table.' LIMIT 0,1';
		
		if( $this->execute( $query ) )
			return $this->count() == 0 ? true : false;
		return false;
	}	
}
?>