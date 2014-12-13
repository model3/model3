<?php

namespace Model3\Controller;

use Model3\Exception\Model3Exception;
use Model3\Registry\Registry;
use Model3\Request\Request;
use Model3\View\View;

abstract class Controller
{

    /**
     *
     * @var View
     */
    protected $view;

    /**
     *
     * @var Request
     */
    protected $_request;

    /**
     * @param $request Request
     */
    public function __construct($request)
    {
        $this->view = new View($request);

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
     *
     * @throws Model3Exception
     * @internal param
     * @return bool
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
            throw new Model3Exception("Action '{$method}' is not defined in class " . get_class($this));
        }
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    public function redirect($path = '', $useBaseUrl = true, $permanent = false)
    {
        $fullPath = $path;
        
        $config = Registry::get('config');
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