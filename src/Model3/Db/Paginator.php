<?php

namespace Model3\Db;

class Paginator extends Adapter
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
	*
	* @param int $page
	*/
	public function setCurrentPage($page)
	{
		$this->_currentPage = $page;
	}
	
	/**
	*
	* @param int $items
	*/
	public function setItemsByPage($items)
	{
		$this->_itemsByPage = $items;
	}
	
	/**
	* \
	* @param string $query
	*/
	public function setQuery($query)
	{
		$this->_query = $query;
	}
	
	/**
	*
	* @param string $query
	*/	
	public function setQueryCount($query)
	{
		$this->_queryCount = $query;
	}
	
	/**
	*
	* @return $this->_db->getAllRows()
	*/
	public function getItems()
	{
		if($query = $this->_db->execute($this->_queryCount))
		{
			if($result = $this->_db->getRow($query, FETCH_ROW))
			{
				$this->_totalItems = $result[0];
				$this->_totalPages = ceil($this->_totalItems / $this->_itemsByPage);
				if($this->_currentPage > $this->_totalPages && $this->_totalPages > 0)
					$this->_currentPage = $this->_totalPages;
				$queryString = $this->_query.' LIMIT '.(($this->_currentPage-1)*$this->_itemsByPage).', '.$this->_itemsByPage;
				if($this->_db->execute($queryString))
				{
					return $this->_db->getAllRows();
				}
			}
		}
		return false;
	}
	
	/**
	*
	* @return $this->_totalPages
	*/
	public function getTotalPages()
	{
		return $this->_totalPages;
	}
}