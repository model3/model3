<?php

namespace Model3;

class Model3_FileSystem
{
	/**
	 *
	 * @param string $path
	 * @param int $mode
	 * @return bool $mkDir
	 */
	public function makeDir($path, $mode = 0755)
	{	
		return @$mkDir = mkdir($path, $mode, true);
 	}

	/**
	 *
	 * @param string $path
	 * @return bool $rmDir
	 */
	public function removeDir($path)
 	{
		return @$rmDir = rmdir($path);
 	}
	
	/**
    *
	* @param string $path
	* @return bool
    */
	public function existDir($path)
	{
		return file_exists($path);
	}
}
?>