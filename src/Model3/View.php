<?php

namespace Model3;

/**
 * Clase View del Model3PHP
 *
 * @package Model3
 * @author Hector Benitez
 * @version 0.3
 * @copyright 2011 Hector Benitez
 */
class Model3_View
{

    /**
     *
     * @var array
     */
    private $_properties;

    /**
     *
     * @var array
     */
    private $_helpers;

    /**
     *
     * @var string
     */
    private $_template;

    /**
     *
     * @var bool
     */
    private $_useTemplate;

    /**
     *
     * @var Model3_HtmlFactory
     */
    private $_htmlFactory;

    /**
     *
     * @var Model3_CssManager
     */
    private $_cssManager;

    /**
     *
     * @var Model3_JsManager
     */
    private $_jsManager;

    /**
     *
     * @var string
     */
    private $_baseUrl;

    /**
     *
     * @var string
     */
    private $_baseUrlPublic;

    /**
     *
     * @var Model3_Request
     */
    private $_request;

    /**
     *
     * @var string
     */
    private $_title;

    /**
     *
     * @var array
     */
    private $_metas;

    const META_NAME = 0;
    const META_HTTP_EQUIV = 1;

    public function __construct($request)
    {
        $this->_request = $request;
        $this->_htmlFactory = new Model3_HtmlFactory;
        $this->_cssManager = new Model3_CssManager;
        $this->_jsManager = new Model3_JsManager;
        $this->_useTemplate = true;
        $this->_properties = array();
        $this->_helpers = array();
        $this->_baseUrl = $request->getBaseUrl();

        $this->setBaseUrlPublic($request->getBaseUrl());
    }

    /*
     * Metodo magico set
     * @param $property
     * @param $value
     */

    public function __set($property, $value)
    {
        $this->_properties[$property] = $value;
    }

    /*
     * Metodo magico get
     * @param $property
     */

    public function __get($property)
    {
        if (array_key_exists($property, $this->_properties))
            return $this->_properties[$property];
        return NULL;
    }

    /*
     * Metodo magico call
     * @param $method
     * @param $arguments
     * @return $this->_properties[$method]
     */

    public function __call($method, $arguments)
    {
        if (!array_key_exists($method, $this->_helpers))
        {
            $class = 'View_Helper_' . $method;
            $this->_helpers[$method] = new $class($this);
        }
        return $this->_helpers[$method];
    }

    /*
     * Metodo helper
     * @param string $helper Nombre del Helper
     * @param array|null $options Arreglo de opciones, solo se utiliza para inicialización
     * @return Model3_View_Helper
     */

    public function helper($helper, $options = null)
    {
        if (!array_key_exists($helper, $this->_helpers))
        {
            $class = 'View_Helper_' . $helper;
            $this->_helpers[$helper] = new $class($this, $options);
            if (!($this->_helpers[$helper] instanceof Model3_View_Helper))
            {
                throw new Exception('The class ' . $class . ' is not a instance of Model3_View_Helper');
            }
        }
        return $this->_helpers[$helper];
    }

    /*
     * Funcion para obtener los elementos del HTMLFactory
     * @return $this->_htmlFactory
     */

    public function getFactory()
    {
        return $this->_htmlFactory;
    }

    /**
     * Funcion para obtener los elementos del CSSManager
     * @return Model3_CssManager
     */
    public function getCssManager()
    {
        return $this->_cssManager;
    }

    /**
     * Funcion para obtener los elementos del JsManager
     * @return Model3_JsManager
     */
    public function getJsManager()
    {
        return $this->_jsManager;
    }

    /*
     * Coloca el template de la vista
     * @param $template
     */

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Regresa el tamplate de la vista
     * Recibe el template general como parametro, en caso de no tener un template particular,
     * regresa el parametro...
     * @param $template
     * @return
     */
    public function getTemplate($template = NULL)
    {
        if (!$this->_useTemplate)
            return NULL;
        if (empty($this->_template))
            return $template;
        return $this->_template;
    }

    /*
     * Usa el template de la vista
     * @param $use
     */

    public function setUseTemplate($use)
    {
        $this->_useTemplate = $use;
    }

    /*
     * Link a una pagina
     * @param $route
     * @return Model3_Site::baseUrl().$route
     */

    public function linkTo($route = '')
    {
        return $this->_baseUrl . $route;
    }

    /**
     *
     * @param array|null $options
     * @param bool $propague
     * @return string
     */
    public function url($options = null, $propague = false)
    {
        $reset = false;
        $strlenController = 0;
        $strlenAction = 0;

        if ($options == null)
        {
            $options = array();
        }

        $url = $this->_baseUrl . '/';

        $config = Model3_Registry::get('config');
        $configData = $config->getArray();
        if ($configData['m3_internationalization']['inter_multilang'] == true)
        {
            if (array_key_exists('lang', $options))
            {
                $url .= $options['lang'];
                $url .= '/';
            }
            else
            {
                $url .= $this->_request->getLang();
                $url .= '/';
            }
        }

        if (array_key_exists('component', $options))
        {
            if ($options['component'] != null)
            {
                $url .= $options['component'];
                $url .= '/';
                $reset = true;
            }
            unset($options['component']);
        }
        else
        {
            if ($this->_request->isComponent())
            {
                $url .= $this->_request->geComponent();
                $url .= '/';
            }
        }

        if (array_key_exists('module', $options))
        {
            if ($options['module'] != null)
            {
                $url .= $options['module'];
                $url .= '/';
                $reset = true;
            }
            unset($options['module']);
        }
        else
        {
            if ($this->_request->isModule())
            {
                $url .= $this->_request->getModule();
                $url .= '/';
            }
        }

        if (array_key_exists('controller', $options))
        {
            if ($options['controller'] != null)
            {
                $url .= $options['controller'];
                if ($options['controller'] == 'Index')
                {
                    $strlenController = strlen($url);
                }
                $reset = true;
            }
            unset($options['controller']);
        }
        else
        {
            if (!$reset)
            {
                $url .= $this->_request->getController();
            }
            else
            {
                $url .= 'Index';
                $strlenController = strlen($url);
            }
        }
        $url .= '/';

        if (array_key_exists('action', $options))
        {
            if ($options['action'] != null)
            {
                $url .= $options['action'];
                if ($options['action'] == 'index')
                {
                    $strlenController = strlen($url);
                }
            }
            unset($options['action']);
        }
        else
        {
            if (!$reset)
            {
                $url .= $this->_request->getAction();
            }
            else
            {
                $url .= 'index';
                $strlenAction = strlen($url);
            }
        }
        $url .= '/';

        if ($propague == true)
        {
            $params = $this->_request->getParams();
            foreach ($params as $key => $param)
            {
                if (array_key_exists($key, $options))
                {
                    if ($options[$key] != null)
                    {
                        $url .= $key . '/';
                        $url .= $options[$key];
                        $url .= '/';
                    }
                    unset($options[$key]);
                }
                else
                {
                    $url .= $key . '/';
                    $url .= $param;
                    $url .= '/';
                }
            }
        }
        foreach ($options as $key => $option)
        {
            $url .= $key . '/' . $option . '/';
        }

        /**
         * Limpiaremos la url en caso de que termine en index/ o en Index/index/
         */
        if ($strlenAction != 0 && ($strlenAction + 1) == strlen($url))
        {
            if ($strlenController != 0 && ($strlenController + 1) == strlen($url))
            {
                $url = substr($url, 0, $strlenController - 5);
            }
            else
            {
                $url = substr($url, 0, $strlenAction - 5);
            }
        }

        return $url;
    }

    /*
     * Base url de la pagina
     * @return Model3_Site::baseUrl()
     */

    public function setBaseUrl($path)
    {
        $this->_baseUrl = $path;
    }

    /*
     * Base url de la pagina
     * @return Model3_Site::baseUrl()
     */

    public function setBaseUrlPublic($path)
    {
        $this->_baseUrlPublic = $path;
        $this->_cssManager->setBaseUrl($path);
        $this->_jsManager->setBaseUrl($path);
    }

    /*
     * Base url de la pagina
     * @return Model3_Site::baseUrl()
     */

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /*
     * Base url de la pagina
     * @return Model3_Site::baseUrl()
     */

    public function getBaseUrlPublic()
    {
        return $this->_baseUrlPublic;
    }

    /*
     * Base url de la pagina
     * @param
     * @return Model3_Site::baseUrl()
     */

    public function escape($txt)
    {
        return htmlentities($txt);
    }

    /**
     * Establece el contenido del tag title para la pagina, y lo regresa en caso de no incluir parametro solo lo regresa
     * @param string $title
     * @return string
     */
    public function headTitle($title = null)
    {
        if ($title != null)
            $this->_title = $title;
        return '<title>' . $this->_title . '</title>' . PHP_EOL;
    }

    /**
     *
     * @param int $type
     * @param string $value
     * @param string $content
     */
    public function addMeta($type, $value, $content)
    {
        $this->_metas[] = array('type' => $type, 'value' => $value, 'content' => $content);
    }

    /**
     *
     * @return string
     */
    public function headMeta()
    {
        $result = '';
        foreach ($this->_metas as $meta)
        {
            $metaText = '<meta ';
            switch ($meta['type'])
            {
                case self::META_NAME:
                    $metaText .= 'name=';
                    break;
                case self::META_HTTP_EQUIV:
                    $metaText .= 'http-equiv=';
                    break;
            }
            $metaText .= '"' . $meta['value'] . '" ';
            $metaText .= 'content="' . $meta['content'] . '" />';
            $result .= $metaText . PHP_EOL;
        }
        return $result;
    }

}