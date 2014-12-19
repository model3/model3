<?php

namespace Model3\Request;

use Model3\Registry\Registry;
use Model3\Site\Site;

class Request
{

    const SCHEME_HTTP = 'http';

    /**
     * Scheme for https
     *
     */
    const SCHEME_HTTPS = 'https';

    protected $_requestUri;
    protected $_baseUrl = null;
    protected $_basePath = null;
    protected $_route;
    protected $_controller;
    protected $_action;
    protected $_params;
    protected $_component;
    protected $_module;
    protected $_multilang;
    protected $_lang;

    public function __construct()
    {
        $this->constructsFromGet();
    }

    public function constructsFromGet()
    {
        $path = array_keys($_GET);
        $this->constructs(isset($path[0]) ? $path[0] : null);
    }

    public function initialize()
    {
        $this->_controller = 'Index';
        $this->_action = 'index';
        $this->_params = array();

        $config = Registry::get('config');
        $configData = $config->getArray();
        $this->_multilang = $configData['m3_internationalization']['inter_multilang'];
        $this->_lang = $configData['m3_internationalization']['inter_default_lang'];
    }

    public function constructs($path)
    {
        $this->initialize();
        if ($path == null || $path == '' || empty($path))
            return;

        $route = rtrim($path, '/');
        $routeParts = preg_split('/\//', $route);

        $idx = 0;

        if ($this->_multilang == true && isset($routeParts[$idx]))
        {
            $this->_lang = $routeParts[$idx];
            $idx++;
        }

        $this->_component = null;
        if (isset($routeParts[$idx]))
        {
            $componentes = Site::getComponentList();
            if (in_array($routeParts[$idx], $componentes))
            {
                $this->_component = $routeParts[$idx];
                $idx++;
            }
        }

        $this->_module = null;
        if (isset($routeParts[$idx]))
        {
            $modulos = Site::getModuleList();
            if (in_array($routeParts[$idx], $modulos))
            {
                $this->_module = $routeParts[$idx];
                $idx++;
            }
        }

        $this->_controller = (isset($routeParts[$idx]) && !empty($routeParts[$idx])) ? $routeParts[$idx] : "Index";
        $idx++;
        $this->_action = (isset($routeParts[$idx]) && !empty($routeParts[$idx])) ? $routeParts[$idx] : "index";
        $idx++;

        for ($cont = 0; $cont < $idx; $cont++)
        {
            array_shift($routeParts);
        }
        $params = array();
        for ($cont = 0; $cont < count($routeParts); $cont += 2)
        {
            if (isset($routeParts[$cont + 1]))
                $params[$routeParts[$cont]] = $routeParts[$cont + 1];
        }
        $this->_params = $params;
    }

    /**
     *
     * @return $this->controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    /**
     *
     * @return $this->action
     */
    public function getAction()
    {
        if (empty($this->_action))
            $this->_action = "index";
        return $this->_action;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     *
     * @param string $params
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    /**
     *
     * @param int $idx
     * @return $this->_params[$idx]
     */
    public function getParam($idx)
    {
        if (!isset($this->_params[$idx]))
            return NULL;
        return $this->_params[$idx];
    }

    /**
     *
     * @return int
     */
    public function numParam()
    {
        if (is_array($this->_params))
            return count($this->_params);
        return 0;
    }

    /**
     *
     * @return $this->params
     */
    public function getComponent()
    {
        return $this->_component;
    }

    /**
     *
     * @return $this->params
     */
    public function isComponent()
    {
        if ($this->_component != null && $this->_component != '')
        {
            return true;
        }
        return false;
    }

    /**
     *
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    public function setModule($module)
    {
        $this->_module = $module;
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function set_lang($lang)
    {
        $this->_lang = $lang;
    }

    /**
     *
     * @return $this->params
     */
    public function isModule()
    {
        if ($this->_module != null && $this->_module != '')
        {
            return true;
        }
        return false;
    }

    /**
     *
     * @return bool
     */
    public function isPost()
    {
        if (empty($_POST))
            return false;
        return true;
    }

    /**
     *
     * @return array
     */
    public function getPost()
    {
        return $_POST;
    }

    public function getServer($key = null, $default = null)
    {
        if (null === $key)
        {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * Retrieve a member of the $_ENV superglobal
     *
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getEnv($key = null, $default = null)
    {
        if (null === $key)
        {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }

    public function getScheme()
    {
        return ($this->getServer('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    /**
     * Get the HTTP host.
     *
     * "Host" ":" host [ ":" port ] ; Section 3.2.2
     * Note the HTTP Host header is not the same as the URI host.
     * It includes the port while the URI host doesn't.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $host = $this->getServer('HTTP_HOST');
        if (!empty($host))
        {
            return $host;
        }

        $scheme = $this->getScheme();
        $name = $this->getServer('SERVER_NAME');
        $port = $this->getServer('SERVER_PORT');

        if (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443))
        {
            return $name;
        }
        else
        {
            return $name . ':' . $port;
        }
    }

    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null)
        {
            if (isset($_SERVER['HTTP_X_REWRITE_URL']))
            { // check this first so IIS will catch
                $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            }
            elseif (
                // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
                isset($_SERVER['IIS_WasUrlRewritten'])
                && $_SERVER['IIS_WasUrlRewritten'] == '1'
                && isset($_SERVER['UNENCODED_URL'])
                && $_SERVER['UNENCODED_URL'] != ''
            )
            {
                $requestUri = $_SERVER['UNENCODED_URL'];
            }
            elseif (isset($_SERVER['REQUEST_URI']))
            {
                $requestUri = $_SERVER['REQUEST_URI'];
                // Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
                $schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
                if (strpos($requestUri, $schemeAndHttpHost) === 0)
                {
                    $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
                }
            }
            elseif (isset($_SERVER['ORIG_PATH_INFO']))
            { // IIS 5.0, PHP as CGI
                $requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING']))
                {
                    $requestUri .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
            else
            {
                return $this;
            }
        }
        elseif (!is_string($requestUri))
        {
            return $this;
        }
        else
        {
            // Set GET items, if available
            if (false !== ($pos = strpos($requestUri, '?')))
            {
                // Get key => value pairs and set $_GET
                $query = substr($requestUri, $pos + 1);
                parse_str($query, $vars);
                $this->setQuery($vars);
            }
        }

        $this->_requestUri = $requestUri;
        return $this;
    }

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (empty($this->_requestUri))
        {
            $this->setRequestUri();
        }

        return $this->_requestUri;
    }

    public function setBaseUrl($baseUrl = null)
    {
        if ((null !== $baseUrl) && !is_string($baseUrl))
        {
            return $this;
        }

        if ($baseUrl === null)
        {
            $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

            if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename)
            {
                $baseUrl = $_SERVER['SCRIPT_NAME'];
            }
            elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename)
            {
                $baseUrl = $_SERVER['PHP_SELF'];
            }
            elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename)
            {
                $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
            }
            else
            {
                // Backtrack up the script_filename to find the portion matching
                // php_self
                $path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
                $file = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
                $segs = explode('/', trim($file, '/'));
                $segs = array_reverse($segs);
                $index = 0;
                $last = count($segs);
                $baseUrl = '';
                do
                {
                    $seg = $segs[$index];
                    $baseUrl = '/' . $seg . $baseUrl;
                    ++$index;
                }
                while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
            }

            // Does the baseUrl have anything in common with the request_uri?
            $requestUri = $this->getRequestUri();

            if (0 === strpos($requestUri, $baseUrl))
            {
                // full $baseUrl matches
                $this->_baseUrl = $baseUrl;
                return $this;
            }

            if (0 === strpos($requestUri, dirname($baseUrl)))
            {
                // directory portion of $baseUrl matches
                $this->_baseUrl = rtrim(dirname($baseUrl), '/');
                return $this;
            }

            $basename = basename($baseUrl);
            if (empty($basename) || !strpos($requestUri, $basename))
            {
                // no match whatsoever; set it blank
                $this->_baseUrl = '';
                return $this;
            }

            // If using mod_rewrite or ISAPI_Rewrite strip the script filename
            // out of baseUrl. $pos !== 0 makes sure it is not matching a value
            // from PATH_INFO or QUERY_STRING
            if ((strlen($requestUri) >= strlen($baseUrl))
                && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0)))
            {
                $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
            }
        }

        $this->_baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    public function getBaseUrl()
    {
        if (null === $this->_baseUrl)
        {
            $this->setBaseUrl();
        }

        return $this->_baseUrl;
    }

    /**
     * Set the base path for the URL
     *
     * @param string|null $basePath
     * @return Request
     */
    public function setBasePath($basePath = null)
    {
        if ($basePath === null)
        {
            $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

            $baseUrl = $this->getBaseUrl();
            if (empty($baseUrl))
            {
                $this->_basePath = '';
                return $this;
            }

            if (basename($baseUrl) === $filename)
            {
                $basePath = dirname($baseUrl);
            }
            else
            {
                $basePath = $baseUrl;
            }
        }

        if (substr(PHP_OS, 0, 3) === 'WIN')
        {
            $basePath = str_replace('\\', '/', $basePath);
        }

        $this->_basePath = rtrim($basePath, '/');
        return $this;
    }

    /**
     * Everything in REQUEST_URI before PATH_INFO not including the filename
     *
     * @return string
     */
    public function getBasePath()
    {
        if (null === $this->_basePath)
        {
            $this->setBasePath();
        }

        return $this->_basePath;
    }

}