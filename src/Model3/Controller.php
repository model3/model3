<?php

/**
 * Clase Page, para el M3
 *
 * Esta clase obtiene todas las acciones del controlador de pagina
 * @package Model3
 * @author Hector Benitez
 * @version 0.3
 */
abstract class Model3_Controller
{

    /**
     *
     * @var Model3_View
     */
    protected $view;

    /**
     *
     * @var Model3_Request
     */
    protected $_request;

    public function __construct($request)
    {
        $this->view = new Model3_View($request);

        $this->_request = $request;
        if ($request->isComponent())
        {
            $this->view->setBaseUrlPublic($this->view->getBaseUrl() . '_components/' . $request->getComponent() . '/');
            $this->view->setBaseUrl($this->view->getBaseUrl() . $request->getComponent() . '/');
        }
    }

    public function init()
    {

    }

    public function postDispatch()
    {

    }

    public function preDispatch()
    {

    }

    /**
     * Esta clase carga el despachador de acciones
     * @param $action La accion a cargar
     * @return bool Regresa true si la accion fue cargada , caso contrario false
     */
    public function dispatch()
    {
        $method = $this->_request->getAction() . 'Action';

        if (method_exists($this, $method))
        {
            $this->preDispatch();
            $this->$method();
            $this->postDispatch();
            return true;
        } else
        {
            throw new Exception("Action '{$method}' is not defined in class " . get_class($this));
        }
        return false;
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     *
     * @return Model3_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    public function redirect($path = '', $useBaseUrl = true, $permanent = false)
    {
        $fullPath = $path;
        
        $config = Model3_Registry::get('config');
        $configData = $config->getArray();
        if ($configData['m3_internationalization']['inter_multilang'] == true)
        {
            $fullPath = $configData['m3_internationalization']['inter_default_lang'] . '/' . $path;
        }
        
        if($useBaseUrl == true)
            $fullPath = $this->_request->getBaseUrl() . '/' . $fullPath;
        if($permanent == true)
        {
            header("HTTP/1.1 301 Moved Permanently");
        }

        header('Location: ' . $fullPath);
        exit;
    }

}