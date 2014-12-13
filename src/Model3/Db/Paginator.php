<?php
/**
* Clase Paginator, para el M3
*
* Esta clase obtiene construye un paginador de datos
* @package LIFE
* @author Hector Benitez
* @version 1.0
*/
class Model3_Db_Paginator extends Model3_Db_Adapter
{
	protected $_currentPage;
	protected $_itemsByPage;
	protected $_totalItems;
	protected $_totalPages;
	protected $_query;
	protected $_queryCount;
	
	public function __construct($db = null)
	{
		parent::__construct($db);
		$this->_currentPage = 1;
		$this->_itemsByPage = 30;
		$this->_totalItems = 0;
		$this->_totalPages = 0;
		$this->_query = '';
		$this->_queryCount = '';
	}
	
	/**
	* Esta clase coloca la pagina actual
	* @param int $page La pagina actual
	* @author Hector Benitez
	* @version 1.0
	*/
	public function setCurrentPage($page)
	{
		$this->_currentPage = $page;
	}
	
	/**
	* Esta clase coloca los elementos por pagina
	* @param int $items el numero de elementos por pagina
	*/
	public function setItemsByPage($items)
	{
		$this->_itemsByPage = $items;
	}
	
	/**
	* Coloca el query para visualizar los datos
	* @param string $query la consulta a la bd
	*/
	public function setQuery($query)
	{
		$this->_query = $query;
	}
	
	/**
	* Coloca el numero de elementos del query
	* @param string $query el string de la consulta a la bd
	*/	
	public function setQueryCount($query)
	{
		$this->_queryCount = $query;
	}
	
	/**
	* Obtiene los elementos del paginador
	* @return $this->_db->getAllRows() si hay datos en la consulta, caso contrario false
	*/
	public function getItems()
	{
		if($consulta = $this->_db->execute($this->_queryCount))
		{
			if($result = $this->_db->getRow($consulta, FETCH_ROW))
			{
				$this->_totalItems = $result[0];
				$this->_totalPages = ceil($this->_totalItems / $this->_itemsByPage);
				if($this->_currentPage > $this->_totalPages && $this->_totalPages > 0)
					$this->_currentPage = $this->_totalPages;
				$query = $this->_query.' LIMIT '.(($this->_currentPage-1)*$this->_itemsByPage).', '.$this->_itemsByPage;
				if($this->_db->execute($query))
				{
					return $this->_db->getAllRows();
				}
			}
		}
		return false;
	}
	
	/**
	* Obtiene el total de paginas
	* @return $this->_totalPages
	*/
	public function getTotalPages()
	{
		return $this->_totalPages;
	}
}