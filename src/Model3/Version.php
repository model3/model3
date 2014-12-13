<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Version
 *
 * @author hector
 */
final class Model3_Version
{
    /**
     * Model3PHP Framework version identification - see compareVersion()
     */
    const VERSION = '0.3.1';

    /**
     * Compare the specified Model3PHP Framework version string $version
     * with the current Model3_Version::VERSION of Model3PHP Framework.
     *
     * @param  string  $version  A version string (e.g. "0.2").
     * @return int           -1 if the $version is older, 0 if they are the same, and +1 if $version is newer.
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }
}
