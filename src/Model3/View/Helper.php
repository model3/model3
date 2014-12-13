<?php

/**
 * Clase View_Helper del M3
 *
 *
 * @author Hector Benitez
 * @version 1.0
 * @copyright 2009 Hector Benitez
 */
class Model3_View_Helper
{
	protected $_view;

	public function __construct($view)
	{
		$this->_view = $view;
	}
}