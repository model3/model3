<?php
/**
 * Table_Adapter
 *
 * @method mixed findBy*(mixed $value) magic finders; @see __call()
 * @method mixed findOneBy*(mixed $value) magic finders; @see __call()
 */
class Model3_Db_Table extends Model3_Db_Paginator
{
	protected $_tableName;
	protected $_primaryKey;
	protected $_fields;
	protected $_rowClass;
	protected $_paginatorMode;

	public function __construct($tableName, $db = NULL)
	{
        parent::__construct($db);
        $this->setPaginatorMode(false)
			->setTableName($tableName)
            ->setRowClass('Model3_Db_Row');
        if(!$this->describe())
            throw new Exception("La tabla $tableName no se pudo inicializar");
        return true;
	}
	
    public function  __call($name, $arguments)
    {
        $lcMethod = strtolower($name);
        if(substr($lcMethod, 0, 6) == 'findby')
        {
            $by = substr($name, 6, strlen($name));
            $method = 'find';
        }
        else if(substr($lcMethod, 0, 9) == 'findoneby')
        {
            $by = substr($name, 9, strlen($name));
            $method = 'findOne';
        }
        if(isset($by))
        {
            if(isset($arguments[0]) == true && $this->validateKey($by) == true)
            {
                if($method == 'findOne')
                    return $this->$method($by, $arguments[0]);
                else
                    return $this->$method($by, $arguments);
            }
        }
        return false;
    }

	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
		$this->describe();
        return $this;
	}
	
	public function getTableName()
	{
		return $this->_tableName;
	}
	
	public function getPrimaryKey($primaryKey)
	{
		return $this->_primaryKey;
	}
	
	public function getFields()
	{
		return $this->_fields;
	}
	
	public function setRowClass($rowClass)
	{
		$this->_rowClass = $rowClass;
        return $this;
	}
	
	public function getRowClass()
	{
		return $this->_rowClass;
	}
	
	public function setPaginatorMode($paginatorMode)
	{
		$this->_paginatorMode = $paginatorMode;
		return $this;
	}
	
	public function getPaginatorMode()
	{
		return $this->_paginatorMode;
	}
	
	//for now, this method is intended just for tables with one primary key
	protected function describe()
	{
		if(is_null($this->_tableName) == false)
		{
			$this->_query = 'DESCRIBE '.$this->_tableName;
            if($this->_db->execute($this->_query) !== false)
			{
				$res = $this->_db->getAllRows();
                unset($this->_fields);
                unset($this->_primaryKey);
                $this->_fields = array();
                foreach($res as $r)
                {
                    $this->_fields[] = $r['Field'];
                    if($r['Key'] == 'PRI')
                    	$this->_primaryKey = $r['Field'];
                }
				return true;
			}
		}
		return false;
	}
		
	//data is an associative array containing the name of the field and its value
	public function insert($data)
	{
		//var_dump($data);
		unset($this->_error);
		if(is_array($data))
		{
			$fields = '';
			$values = '';
			reset($data);
			for($counter = 0, $maxIndex = count($data), $valid = true; $counter < $maxIndex && $valid == true; $counter ++)
			{
				$elementKey = key($data);
				$elementKey = $this->_db->escape($elementKey);
				$valid = in_array($elementKey, $this->_fields);
				if($valid == true)
				{
                    $element = $data[$elementKey];
                    $element = $this->_db->escape($element);
					if(is_object($element) == true)
					{
						$valid = false;
						$this->_error = 'field: '.$elementKey.'is an object (this method just accept strings)';
					}
					else
					{
						if(is_numeric($element) == false)
							$values .= '\''.$element.'\'';
						else
							$values .= $element;
						$fields .= $elementKey;
						if(($counter + 1) < $maxIndex)
						{
							$fields .= ',';
							$values .= ',';
						}
						next($data);
					}
				}
				else
				{
					$this->_error = 'field: '.$elementKey.' is not defined in '.$this->_tableName;
				}
			}
			if($valid == true)
			{
				$this->_query = 'INSERT INTO '.$this->_tableName.'('.$fields.') VALUES('.$values.')';
				//var_dump($this->_query);
				if($this->_db->execute($this->_query) !== false)
				{
					//echo 'entro';
					return $this->_db->insertId();
				}
				else
				{
					$this->_error = $this->_db->errorStr();
					//echo $this->_error;
				}
			}
		}
		return false;
	}

	protected function validateKey($key)
	{
		$response = false;
        if(is_array($key) == true)
        {
        	$this->_error = '$key param cannot be an array';
        }
        elseif(in_array($key, $this->_fields) === false)
        {
            $this->_error = '$key value is not a valid field';
        }
        else
        {
        	$response = true;
        }
		return $response;
	}

	protected function validateValues($values)
	{
		$response = false;
    	if(is_array($values) === false)
    	{
    		$this->_error = 'Values is not an array';
    	}
    	elseif(count($values) == 0)
		{
			$this->_error = 'The size of the array $values has to be greater than 0';
		}
		else
		{
        	$response = true;
        }
		return $response;
	}
	
	protected function basicCheck($key, $values)
	{
        unset($this->_error);
        $validKey = $this->validateKey($key);
        if($validKey == true)
        {
	        $validValues = $this->validateValues($values);
	        if($validValues == true)
	        	return true;
        }
		return false;
	}
	
	//compose query
    protected function composeQuery($key, $values)
    {
    	$query = '';
        for($counter = 0, $maxIndex = count($values); $counter < $maxIndex; $counter ++)
        {
            if(is_numeric($values[$counter]))
                $query .= $key.' = '.$values[$counter];
            else
                $query .= $key.' = \''.$values[$counter].'\'';
            if(($counter + 1) < $maxIndex)
                $query .= ' OR ';
        }
        return $query;
    }

	protected function transformToRowset($rowsArray)
	{
        $rowSet = array();
        foreach($rowsArray as $r)
        {
            $row = new $this->_rowClass;
            $row->setData($r);
            $rowSet[] = $row;
        }
        return $rowSet;
	}
	
	protected function findExecute()
	{
		if($this->_paginatorMode == true)
		{
			$this->setQueryCount($this->_query);
			$rows = parent::getItems();
		}
		else
		{
			$resource = $this->_db->execute($this->_query);
			$rows = false;
			if($resource !== false)
			{
				$rows = $this->_db->getAllRows();								
			}
		}
		
        if($rows !== false)
        {
        	return $this->transformToRowset($rows);
        }
		else
		{
			$this->_error = $this->_db->errorStr();
		}
		return false;
	}   
	 
	//return a rowSet
	//for now this method is just accepting a single key value (no arrays allowed) 
	public function find($key, $values, $order = null)
	{
        if(is_array($values) == false)
            $arrayOfValues = array($values);
        else
            $arrayOfValues = $values;
		$orderString = '';
        if($this->basicCheck($key, $arrayOfValues) == true)
        {
			$orderChecked = strcasecmp('DESC', $order) == 0 ? 'DESC' : '';
			$orderString = ' ORDER BY '.$key.' '.$orderChecked;
            $this->_query = 'SELECT * FROM '.$this->_tableName.' WHERE '.$this->composeQuery($key, $arrayOfValues).$orderString;
            return $this->findExecute();
        }
        return false;
	}
	
	public function findAll($key = null, $order = null)
	{
		$orderString = '';
		if($this->validateKey($key) == true)
		{
			$orderChecked = strcasecmp('DESC', $order) == 0 ? 'DESC' : '';
			$orderString = ' ORDER BY '.$key.' '.$orderChecked;
		}
		$this->_query = 'SELECT * FROM '.$this->_tableName.' '.$orderString;
		return $this->findExecute();
	}
	
	public function findWhere($where)
	{
		$this->_query = 'SELECT * FROM '.$this->_tableName.' WHERE '.$where;
		//echo $this->_query;
		return $this->findExecute();
	}
	
    public function findOne($key, $values)
    {
        if(is_array($values) == false)
            $arrayOfValues = array($values);
        else
            $arrayOfValues = $values;
        $orderString = '';
        if($this->basicCheck($key, $arrayOfValues) == true)
        {
            $orderChecked = strcasecmp('DESC', $orderString) == 0 ? 'DESC' : '';
            $orderString = ' ORDER BY '.$key.' '.$orderChecked;
            $this->_query = 'SELECT * FROM '.$this->_tableName.' WHERE '.$this->composeQuery($key, $arrayOfValues).$orderString;
            $res = $this->findExecute();
            if(is_array($res) == true && count($res) > 0)
                return $res[0];
        }
        return false;
    }

	protected function basicExecute()
	{		
        if($this->_db->execute($this->_query) !== false)
        {
            return true;
		}
		else
		{
			$this->_error = $this->_db->errorStr();
		}
		return false;		
	}
	
	public function delete($key, $values)
	{
        if(is_array($values) == false)
            $arrayOfValues = array($values);
        else
            $arrayOfValues = $values;
        if($this->basicCheck($key, $arrayOfValues) == true)
        {
            $this->_query = 'DELETE FROM '.$this->_tableName.' WHERE '.$this->composeQuery($key, $arrayOfValues);
            return $this->basicExecute();
        }
		return false;
	}
	
	public function deleteAll()
	{
        $this->_query = 'DELETE FROM '.$this->_tableName;
        return $this->basicExecute();
	}
	
	public function deleteWhere($where)
	{
        $this->_query = 'DELETE FROM '.$this->_tableName.' WHERE '.$where;
        return $this->basicExecute();
	}
	
	protected function parseSetParams($data)
	{
		$setParams = '';
		$cont = 0;
		$max = count($data);
		foreach($data as $key => $field)
		{
			$setParams .= $key.' = \''.$field.'\'';
			if(($cont + 1) < $max)
				$setParams .= ', ';
			$cont ++;
		}
	//	$setParams = ".$setParams.";
		return $setParams;
	}
	
	//TODO crear validacion del arreglo data, tanto para este metodo como para insert
    public function update($key, $values, $data)
    {
        if(is_array($values) == false)
            $arrayOfValues = array($values);
        else
            $arrayOfValues = $values;
        if($this->basicCheck($key, $arrayOfValues) == true)
        {
            $this->_query = 'UPDATE '.$this->_tableName.' SET '.$this->parseSetParams($data).' WHERE '.$this->composeQuery($key, $arrayOfValues);
            return $this->basicExecute();
        }
        return false;
    }
    
    public function updateAll($data)
    {
        $this->_query = 'UPDATE '.$this->_tableName.' SET '.$this->parseSetParams($data);
        return $this->basicExecute();
    }
    
    public function updateWhere($data, $where)
	{
        $this->_query = 'UPDATE '.$this->_tableName.' SET '.$this->parseSetParams($data).' WHERE '.$where;
		//echo $this->_query ;
		return $this->basicExecute();
	}
	
	//this method was overloaded to avoid it's use
	public function getItems()
	{
		return true;
	}
}