<?php

/**
 * Clase CssManager para el uso de los CSS del sistema
 * @package Model3
 * @author Hector Benitez
 * @version 0.03
 * @copyright 2011 Hector Benitez
 */
class Model3_CssManager
{

    private static $_baseDir = '';
    private $_baseUrl;
    private $_cssArray;

    public function __construct($base = '')
    {
        $this->_baseUrl = $base;
        $this->_cssArray = array();
    }

    static public function setBaseDir($path = '')
    {
        self::$_baseDir = $path;
    }

    static public function getBaseDir()
    {
        return self::$_baseDir;
    }

    public function setBaseUrl($path = '')
    {
        $this->_baseUrl = $path;
    }

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /*
     * Agrega un archivo CSS
     * @param $script
     * @param $ignoreBase
     */

    public function addCss($style, $media = 'screen', $ignoreBase = false, $conditional = null)
    {
        $this->_cssArray[] = array('style' => $style, 'media' => $media, 'ignoreBase' => $ignoreBase, 'conditional' => $conditional);
    }

    /*
     * Carga los java script
     */

    public function hasCss()
    {
        if (count($this->_cssArray) > 0)
            return true;
        return false;
    }

    /**
     * Funcion para cargar los archivos CSS
     *
     * @param string $filename
     */
    function loadCssFile($filename, $media = 'screen', $ignoreBase = false, $conditional = null)
    {
        if ($ignoreBase)
        {
            $result = '<link rel="stylesheet" href="' . $this->_baseUrl . $filename . '" type="text/css" media="'
                    .$media.'" />' . PHP_EOL;
        }
        else
        {
            $result = '<link rel="stylesheet" href="' . $this->_baseUrl . self::$_baseDir . $filename
                    . '" type="text/css" media="'.$media.'" />' . PHP_EOL;
        }
        if($conditional != null)
        {
            $result = '<!--[if '.$conditional.']>'.$result.'<![endif]-->' . PHP_EOL;
        }
        echo $result;
    }

    /*
     * Carga los CSS
     */

    public function loadCss()
    {
        foreach ($this->_cssArray as $style)
        {
            $this->loadCssFile($style['style'], $style['media'], $style['ignoreBase'], $style['conditional']);
        }
    }

}