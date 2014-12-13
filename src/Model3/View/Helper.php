<?php

namespace Model3\View;

class Helper
{
	protected $_view;

	public function __construct($view)
	{
		$this->_view = $view;
	}
}