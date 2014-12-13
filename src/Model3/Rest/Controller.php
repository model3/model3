<?php
/**
 * Clase Page, para el M3
 *
 * Esta clase obtiene todas las acciones del controlador de pagina
 * @package LIFE
 * @author Hector Benitez
 * @version 1.0
 */

abstract class Model3_Rest_Controller extends Model3_Controller
{
    public $view;
    protected $_request;
    protected $_params;
    protected $_accepted;

    public function __construct($request)
    {
        $this->view = new Model3_View($request);

        $this->_request = $request;

        if( strpos($_SERVER['HTTP_ACCEPT'], 'json') )
        {
            $this->_accepted = 'json';
        }
        else if( strpos($_SERVER['HTTP_ACCEPT'], 'xml') )
        {
            $this->_accepted = 'xml';
        }
        else
        {
            $this->sendResponse( 400 );
        }
        $this->setParams($this->_request->getParams());

        if($request->isComponent())
        {
            $this->view->setBaseUrlPublic($this->view->getBaseUrl().'_components/'.$request->getComponent().'/');
            $this->view->setBaseUrl($this->view->getBaseUrl().$request->getComponent().'/');
        }
    }

    /**
     * Esta clase carga el despachador de acciones
     * @param $action La accion a cargar
     * @return bool Regresa true si la accion fue cargada , caso contrario false
     */
    public function dispatch()
    {
        if( $this->validate($this->_request->getApiKey() ) === true )
        {
            $method = $this->_request->getMethod().'Method';
            if(method_exists($this, $method))
            {
                $this->$method();
                return true;
            }
            else
            {
                //throw new Exception( "Method '{$method}' is not defined in class ".get_class($this));
                $this->view->setUseTemplate( false );
                $this->sendResponse( 405 );
            }
        }
        else
        {
            $this->view->setUseTemplate( false );
            $this->invalidReturn();
        }
        return false;
    }

    protected function invalidReturn()
    {
        $this->sendResponse( 401 );
    }

    abstract protected function validate( $apiKey );

    public function getData()
    {
        parse_str(file_get_contents('php://input'), $data);
        return $data;
    }

    public function sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        $this->_request->sendResponse( $status, $body, $content_type );
    }

    public function getHttpAccept()
    {
        return $this->_request->getHttpAccept();
    }
}

