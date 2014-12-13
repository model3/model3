<?php

namespace Model3\View;

use Model3\Exception\Model3Exception;
use Model3\Manager\CssManager;
use Model3\Manager\JsManager;
use Model3\Request\Request;

class View
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
     * @var HtmlFactory
     */
    private $_htmlFactory;

    /**
     *
     * @var CssManager
     */
    private $_cssManager;

    /**
     *
     * @var JsManager
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
     * @var Request
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
        $this->_htmlFactory = new HtmlFactory();
        $this->_cssManager = new CssManager;
        $this->_jsManager = new JsManager;
        $this->_useTemplate = true;
        $this->_properties = array();
        $this->_helpers = array();
        $this->_baseUrl = $request->getBaseUrl();

        $this->setBaseUrlPublic($request->getBaseUrl());
    }

    public function __set($property, $value)
    {
        $this->_properties[$property] = $value;
    }

    public function __get($property)
    {
        if (array_key_exists($property, $this->_properties))
            return $this->_properties[$property];
        return NULL;
    }

    public function __call($method, $arguments)
    {
        if (!array_key_exists($method, $this->_helpers))
        {
            $class = 'View_Helper_' . $method;
            $this->_helpers[$method] = new $class($this);
        }
        return $this->_helpers[$method];
    }

    public function helper($helper, $options = null)
    {
        if (!array_key_exists($helper, $this->_helpers))
        {
            $class = 'View_Helper_' . $helper;
            $this->_helpers[$helper] = new $class($this, $options);
            if (!($this->_helpers[$helper] instanceof Helper))
            {
                throw new Model3Exception('The class ' . $class . ' is not a instance of Model3_View_Helper');
            }
        }
        return $this->_helpers[$helper];
    }

    public function getFactory()
    {
        return $this->_htmlFactory;
    }

    public function getCssManager()
    {
        return $this->_cssManager;
    }

    public function getJsManager()
    {
        return $this->_jsManager;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function getTemplate($template = NULL)
    {
        if (!$this->_useTemplate)
            return NULL;
        if (empty($this->_template))
            return $template;
        return $this->_template;
    }

    public function setUseTemplate($use)
    {
        $this->_useTemplate = $use;
    }

    public function linkTo($route = '')
    {
        return $this->_baseUrl . $route;
    }

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

    public function setBaseUrl($path)
    {
        $this->_baseUrl = $path;
    }

    public function setBaseUrlPublic($path)
    {
        $this->_baseUrlPublic = $path;
        $this->_cssManager->setBaseUrl($path);
        $this->_jsManager->setBaseUrl($path);
    }

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    public function getBaseUrlPublic()
    {
        return $this->_baseUrlPublic;
    }

    public function escape($txt)
    {
        return htmlentities($txt);
    }

    public function headTitle($title = null)
    {
        if ($title != null)
            $this->_title = $title;
        return '<title>' . $this->_title . '</title>' . PHP_EOL;
    }

    public function addMeta($type, $value, $content)
    {
        $this->_metas[] = array('type' => $type, 'value' => $value, 'content' => $content);
    }

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