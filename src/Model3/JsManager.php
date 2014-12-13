<?php

/**
 * Clase JsManager, para el M3
 *
 * @package Model3
 * @author Hector Benitez
 * @version 0.3
 */
class Model3_JsManager
{

    /**
     *
     * @var string
     */
    private static $_baseDir = '';
    /**
     *
     * @var string
     */
    private $_baseUrl;
    /**
     *
     * @var array
     */
    private $_jsArray;
    /**
     *
     * @var array
     */
    private $_jsVars;

    /**
     *
     * @param string $base
     */
    public function __construct($base = '')
    {
        $this->_baseUrl = $base;
        $this->_jsArray = array();
        $this->_jsVars = array();
    }

    /**
     *
     * @param string $path
     */
    static public function setBaseDir($path = '')
    {
        self::$_baseDir = $path;
    }

    /**
     *
     * @return string
     */
    static public function getBaseDir()
    {
        return self::$_baseDir;
    }

    /**
     *
     * @param string $path
     */
    public function setBaseUrl($path = '')
    {
        $this->_baseUrl = $path;
    }

    /**
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     *
     * @param string $script
     * @param bool $ignoreBase
     */
    public function addJs($script, $ignoreBase = false)
    {
        $this->_jsArray[] = array('script' => $script, 'ignoreBase' => $ignoreBase);
    }

    /**
     *
     * @return bool
     */
    public function hasJs()
    {
        if (count($this->_jsArray) > 0 || count($this->_jsVars) > 0)
            return true;
        return false;
    }

    /**
     *
     * @param string $filename
     * @param bool $ignoreBase
     */
    function loadJsFile($filename, $ignoreBase = false)
    {
        if ($ignoreBase)
            echo '<script type="text/javascript" src="' . $this->_baseUrl . $filename . '"></script>' . PHP_EOL;
        else
            echo '<script type="text/javascript" src="' . $this->_baseUrl . self::$_baseDir . $filename . '"></script>' . PHP_EOL;
    }

    /**
     *
     */
    public function loadJs()
    {
        if (count($this->_jsVars) > 0)
        {
            echo '<script type="text/javascript">' . PHP_EOL;
            foreach ($this->_jsVars as $key => $value)
            {
                echo $key . ' = ' . $value . ';' . PHP_EOL;
            }
            echo '</script>' . PHP_EOL;
        }

        foreach ($this->_jsArray as $script)
            $this->loadJsFile($script['script'], $script['ignoreBase']);
    }

    /**
     *
     * @param string $name
     * @param $value
     */
    public function addJsVar($name, $value)
    {
        $this->_jsVars[$name] = $value;
    }

}