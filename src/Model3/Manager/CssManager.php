<?php

namespace Model3\Manager;

class CssManager
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

    public function addCss($style, $media = 'screen', $ignoreBase = false, $conditional = null)
    {
        $this->_cssArray[] = array('style' => $style, 'media' => $media, 'ignoreBase' => $ignoreBase, 'conditional' => $conditional);
    }

    public function hasCss()
    {
        if (count($this->_cssArray) > 0)
            return true;
        return false;
    }

    /**
     *
     * @param string $filename
     * @param string $media
     * @param bool $ignoreBase
     * @param null $conditional
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

    public function loadCss()
    {
        foreach ($this->_cssArray as $style)
        {
            $this->loadCssFile($style['style'], $style['media'], $style['ignoreBase'], $style['conditional']);
        }
    }

}