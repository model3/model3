<?php

namespace Model3\Site;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration;
use Model3\Config\Config;
use Model3\Exception\Model3Exception;
use Model3\Manager\CssManager;
use Model3\Manager\JsManager;
use Model3\Registry\Registry;
use Model3\Request\Request;

class Site
{

    private static $_modules = array();
    private static $_components = array();
    private static $_plugins = array();
    /**
     *
     * @var Request
     */
    private static $_request = null;

    public static function initSite($configFile, $component = '')
    {
        $config = self::loadConfigFile($configFile);
        $configData = $config->getArray();

        $emanagers = array();
        foreach ($configData as $key => $data)
        {
            if (strpos($key, 'db_') === 0)
            {
                $host = $data['db_host'];
                $user = $data['db_user'];
                $pass = $data['db_pass'];
                $name = $data['db_name'];
                $cnx = $data['cnx_name'];

                $cache = new \Doctrine\Common\Cache\ArrayCache;
                $config = new Configuration;
                $config->setMetadataCacheImpl($cache);
                $driverImpl = $config->newDefaultAnnotationDriver($data['entities_dir']);
                $config->setMetadataDriverImpl($driverImpl);
                $config->setQueryCacheImpl($cache);
                $config->setProxyDir($data['proxies_dir']);
                $config->setProxyNamespace($configData['general']['app_name'] . '\\' . $cnx . '\Proxies');
                $config->setAutoGenerateProxyClasses(true);

                /**
                 * Por el momento Model3 se basa en proyectos orientados a MySQL
                 */
                $connectionOptions = array(
                    'dbname' => $name,
                    'user' => $user,
                    'password' => $pass,
                    'host' => $host,
                    'driver' => 'pdo_mysql'
                );

                $emanagers[$cnx] = EntityManager::create($connectionOptions, $config);
            }
        }

        Registry::getInstance()->set('databases', $emanagers);

        if (isset($configData['m3_public_settings']['css_dir']))
        {
            CssManager::setBaseDir($configData['m3_public_settings']['css_dir']);
        }

        if (isset($configData['m3_public_settings']['js_dir']))
        {
            JsManager::setBaseDir($configData['m3_public_settings']['js_dir']);
        }

        self::clearPluginList();

        $claseBootstrap = 'App\Bootstrap';
        $bootstrap = new $claseBootstrap;
        $bootstrap->init();
    }

    public static function loadConfigFile($configFile)
    {
        $config = new Config($configFile);
        $registry = Registry::getInstance();
        $registry->set('config', $config);

        return $config;
    }

    public static function runController()
    {
        $registry = Registry::getInstance();
        $config = $registry->get('config');
        $carray = $config->getArray();

        if (self::$_request->isModule())
        {
            $class = 'App\\Controller\\' . self::$_request->getModule() . '_' . self::$_request->getController() . 'Controller';
        }
        else
        {
            $class = 'App\\Controller\\' . self::$_request->getController() . 'Controller';
        }
        if ($carray['general']['debug'])
        {
            $controller = new $class(self::$_request);
            $controller->init();
            $controller->dispatch();
        }
        else
        {

            try
            {
                $controller = new $class(self::$_request);
                $controller->init();
                $controller->dispatch();
            }
            catch (Exception $e)
            {
                /* @var $request Request */
                self::$_request->setController($carray['general']['error_controller']);
                self::$_request->setAction($carray['general']['error_action']);
                $class = self::$_request->getController() . 'Controller';
                $controller = new $class(self::$_request);
                $controller->init();
                $controller->dispatch();
            }
        }

        return $controller;
    }

    public static function preController()
    {
        $plugins = self::getPluginList();
        foreach ($plugins as $plugin)
        {
            $plugin->onPreDispatch(self::$_request);
        }
    }

    public static function postController()
    {
        $plugins = self::getPluginList();
        foreach ($plugins as $plugin)
        {
            $plugin->onPostDispatch(self::$_request);
        }
    }

    public static function dispatch($request)
    {
        $registry = Registry::getInstance();
        $config = $registry->get('config');
        $carray = $config->getArray();
        self::$_request = $request;

        if (self::$_request->isComponent())
        {
            $componente = self::$_request->getComponent();

            restore_include_path();
            set_include_path('../lib/' . PATH_SEPARATOR . get_include_path());
            set_include_path('../app/Components/' . $componente . '/app/' . PATH_SEPARATOR . get_include_path());
            set_include_path('../app/Components/' . $componente . '/app/Controller/' . PATH_SEPARATOR . get_include_path());
            set_include_path('../app/Model/' . PATH_SEPARATOR . get_include_path());

            self::initSite('../app/Components/' . $componente . '/app/Config/config.ini', $componente);

            self::$_request->constructsFromGet();
        }

        if ($carray['general']['debug'] != true)
            ob_start();

        $controller = self::$_request->getController();
        $action = self::$_request->getAction();
        $params = self::$_request->getParams();

        self::preController();
        $app = self::runController();

        self::postController();

        if ($carray['general']['debug'] != true)
            ob_end_clean();

        // Manage view
        ob_start();

        $layout = NULL;
        if (!empty($carray['general']['layout']))
            $layout = $carray['general']['layout'];
        $template = $app->getView()->getTemplate($layout);
        self::loadTemplate($app->getView(), $template);

        $output = ob_get_clean();
        echo $output;
    }

    public static function loadTemplate($view, $layout = NULL)
    {
        if (!empty($layout))
        {
            ob_start();
        }

        $exists = false;
        $absController = self::$_request->getController();
        if (self::$_request->isModule())
        {
            $absController = self::$_request->getModule() . '_' . self::$_request->getController();
        }
        $templatefile = '../src/App/View/Scripts/' . str_replace("_", "/", $absController) . '/' . self::$_request->getAction() . '.php';
        $exists = file_exists($templatefile);
        if ($exists)
        {
            include_once($templatefile);
        }


        if (!$exists)
        {
            $templatefile = '../src/App/View/Scripts/' . str_replace("_", "/", $absController) . '/_default.php';
            $exists = file_exists($templatefile);
            if ($exists)
            {
                include_once($templatefile);
            }
        }

        if (!$exists)
            throw new Model3Exception("View '{$templatefile}' is not found in View/Scripts/ directory.");

        if (!empty($layout))
        {
            $layoutdata = ob_get_clean();
            $layoutfile = 'View/Layout/' . $layout . '.php';

            $path = explode(PATH_SEPARATOR, get_include_path());
            foreach ($path as $tryThis)
            {
                $exists = file_exists($tryThis . $layoutfile);
                if ($exists)
                {
                    include_once($tryThis . $layoutfile);
                    break;
                }
            }

            if (!$exists)
                throw new Model3Exception('Template ' . $layoutfile . ' is not found in View/Layout directory.');
        }
    }

    public static function baseUrl()
    {
        $registry = Registry::getInstance();
        $config = $registry->get('config');
        $carray = $config->getArray();
        return $carray['general']['url'];
    }

    public static function linkTo($path)
    {
        return self::baseUrl() . $path;
    }

    public static function registerComponent($name)
    {
        self::$_components[] = $name;
    }

    public static function getComponentList()
    {
        return self::$_components;
    }

    public static function registerModule($name)
    {
        self::$_modules[] = $name;
    }

    public static function getModuleList()
    {
        return self::$_modules;
    }

    public static function registerPlugin($name, $plugin)
    {
        self::$_plugins[$name] = $plugin;
    }

    public static function getPluginList()
    {
        return self::$_plugins;
    }

    public static function clearPluginList()
    {
        self::$_plugins = array();
    }

}
