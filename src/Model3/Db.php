<?php
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
* Constantes para la obtencion de filas (fetchmode)
* FETCH_ASSOC - La fila es un arreglo asociativo
*/
define("FETCH_ASSOC",1);
/**
* Constantes para la obtencion de filas (fetchmode)
* FETCH_ROW - La fila es un arreglo numerico
*/
define("FETCH_ROW",2);
/**
* Constantes para la obtencion de filas (fetchmode)
* FETCH_BOTH - La fila es un arreglo asociativo y numerico
*/
define("FETCH_BOTH",3);
/**
* Constantes para la obtencion de filas (fetchmode)
* FETCH_OBJECT - La fila es un objeto
*/
define("FETCH_OBJECT",4);

/**
* Clase Bd, esta clase controlara la conexi�n al servidor MySQL
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyright 2008 Hector Benitez
*/
class Model3_Db
{
	/**
    * Variables de conexi�n
    */
	private $_db;
	private $_server;
	private $_user;
	private $_pass;
	
	private $_cnx = false;
	private $_results = array();
	private $_last = null;
	
	/**
    * Control de errores
    */
	private $_errno = 0;
	private $_error = '';

	/**
    * Constructor de la clase
	* <code>
	* <?php
	* $bd = new Bd('localhost','root','123456');
	* ?>
	* </code>
	* @param string $host La direccion IP o el nombre del servidor donde se encuentra la base de datos
	* @param string $user Nombre de usuario para ingresar a la base de datos
	* @param string $pass Password para ingresar a la base de datos
    */
	public function __construct($config)
	{
		$this->_server = $config['host'];
		$this->_user = $config['user'];
		$this->_pass = $config['pass']; 
		$this->_db = $config['name']; 
	}
	
	/**
    * Se conecta a la base de datos, con los parametros que se le asignaron en el constructor
	* <code>
	* <?php
	* $bd->connect();
	* ?>
	* </code>
	* @return bool|resource Regresa el id del recurso generado, en caso de fallar regresa false
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
    * Cierra la conexi�n
	* <code>
	* <?php
	* $bd->close();
	* ?>
	* </code>
	* @return bool Regresa true en caso de exito, regresa false en caso de error o de no tener abierta la conexi�n
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
		$this->_error = 'No existe una conexi�n abierta';
		return false;
	}
	
	/**
    * Indica si tenemos una conexi�n abierta
	* <code>
	* <?php
	* if($bd->isOpen())
	* 	echo 'Conectado';
	* else
	* 	echo 'No conectado';
	* ?>
	* </code>
	* @return bool Regresa true ed tener abierta la conexi�n
    */
	public function isOpen()
	{
		if($this->_cnx)
			return true;
		else
			return false;
	}
	
	/**
    * Ejecuta una sentencia SQL	
	* Esta funcion ejecuta una sentencia SQL, en caso de ser un SELECT nos regresara un id, 
	* que debemos usar en las funciones que llamamos...
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* ?>
	* </code>
	* @param string $sql La sentencia SQL que queremos ejecutar
	* @return bool|int En error regresa false, en un SELECT regresa el id que lo identificara, en otro caso, el num. de filas afectadas
    */
	public function execute($sql)
	{
		if(!$this->isOpen())
		{
			$this->_errno = 0;
			$this->_error = 'No se ha conectado a la base de datos.';
			return false;
		}
		$parts = preg_split('/ /',trim($sql));
		$type = strtolower($parts[0]);
		
		// Este replace es para sentencias que comienzan con (Select... por ejemplo UNION...
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
    * Cuenta el numero de filas regresadas en la consulta dada...
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* $bd->count($consulta); // podria omitir el parametro por ser la ultima consulta
	* ?>
	* </code>
	* @param int $res El identificador del resultado que queremos contar, en caso de null usara usara el mas reciente
	* @return int Regresa el numero de filas que tiene el resultado, en caso de error regresa 0
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
    * Escapa una cadena para consulta	
	* Agrega lo necesario para que la cadena de consulta tenga una sintaxis valida dentro de PHP/MySQL
	* <code>
	* <?php
	* $query = $bd->escape("SELECT * FROM Table01 WHERE id = 'id1'");
	* ?>
	* </code>
	* @param string $sql La sentencia SQL que queremos ejecutar
	* @return string La cadena resultante
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
    * El numero de filas afectadas por la ultima accion del servidor MySQL
	* <code>
	* <?php
	* $consulta = $bd->execute('DELETE FROM Table01');
	* echo 'Eliminados: '.$bd->affectedRows();
	* ?>
	* </code>
	* @return bool|int En error regresa false, de otro modo, el numero de filas afectadas por la ultima operaci�n
    */
	public function affectedRows()
	{
		if(!$this->isOpen())
			return false;
		return mysql_affected_rows($this->_cnx);
	}
	
	/**
    * El ultimo Id insertado por el servidor
	* <code>
	* <?php
	* $consulta = $bd->execute('INSERT INTO Table01(nombre) VALUES('Hector')');
	* echo 'Ultimo id insertado: '.$bd->insertId();
	* ?>
	* </code>
	* @return bool|int En error regresa false, de otro modo, el ultimo Id insertado en el servidor
    */
	public function insertId()
	{
		if(!$this->isOpen())
			return false;
		return mysql_insert_id($this->_cnx);
	}
	
	/**
    * Regresa una fila
	* Obtiene una fila y avanza el apuntador del resultado, la fila sera dada de acuerdo al valor del segundo parametro
	* que puede ser FETCH_ASSOC, FETCH_ROW, FETCH_BOTH o FETCH_OBJECT, los mas comunes son FETCH_ASSOC que devuelve un 
	* arreglo asociativo y FETCH_OBJECT que devuelve un objeto
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* while($fila = $bd->getRow($consulta))
	* 	echo 'Valor: '.$fila['nombre'];
	* ?>
	* </code>	
	* @param int $res El identificador del resultado que queremos usar, en caso de null usara usara el mas reciente
	* @param int $fetchmode El modo en el cual queremos regresar la fila
	* @return mixed|bool El objeto que regresa depende de $fetchmode, en caso de error o de llegar al final regresa false
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
    * Regresa la fila de la posicion indicada
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* $fila = $bd->getRowAt($consulta, 5))
	* echo 'Valor 5: '.$fila['nombre'];
	* ?>
	* </code>
	* @param int $res El identificador del resultado que queremos usar, en caso de null usara usara el mas reciente
	* @param int $offset La posicion que queremos usar
	* @param int $fetchmode El modo en el cual queremos regresar la fila, consulte getRow...
	* @return mixed|bool El objeto que regresa depende de $fetchmode, en caso de error o de llegar al final regresa false
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
    * Mueve el apuntador del bloque de resultados al inicio
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* while($fila = $bd->getRow($consulta))
	* 	echo 'Valor: '.$fila['nombre'];
	* $bd->rewind($consulta); // volvemos al principio
	* while($fila = $bd->getRow($consulta)) // Puedo ejecutar esta parte de nuevo...
	* 	echo 'Valor: '.$fila['nombre'];
	* ?>
	* </code>
	* @param int $res El identificador del resultado que queremos usar, en caso de null usara usara el mas reciente
	* @return bool En caso de error regresa false, en otro caso regresa true
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
    * Regresa un arreglo de filas, x filas a partir de n posicion
	* <code>
	* <?php
	* $consulta = $bd->execute('SELECT * FROM Table01');
	* $filas = $bd->getRows($consulta, 5, 5))
	* echo 'Valor 2,5: '.$fila[2]['nombre'];
	* ?>
	* </code>
	* @param int $res El identificador del resultado que queremos usar, en caso de null usara usara el mas reciente
	* @param int $start La posicion inicial
	* @param int $count El numero de filas que queremos
	* @param int $fetchmode El modo en el cual queremos regresar la fila, consulte getRow...
	* @return array|bool El arreglo de objetos que regresa depende de $fetchmode, en caso de error regresa false
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
    * Regresa una cadena formateada que contiene el numero de error de mysql y su mensaje
	* <code>
	* <?php
	* if(!$bd->connect())
	* {
	* 	echo 'Error: '.$bd->errorStr();
	* }	
	* ?>
	* </code>
	* @return string Regresa una cadena con el numero de error y su descripci�n
    */
	public function errorStr()
	{
		return 'Error No: '.mysql_errno().' Mensaje: '.mysql_error();;
	}
	
	/**
    * El destructor de la clase
	* Libera todos los bloques de resultados y cierra la conexi�n en caso de tenerla abierta...
    */
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