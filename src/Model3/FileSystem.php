<?php
/**
* Clase FileSystem, para M3
*
* Esta clase especifica una capa de datos basada en MySQL Server 5.x
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyright 2008 Hector Benitez
*/
class Model3_FileSystem
{
	/**
    * Crea un directorio con su permiso
	*@param string $path la ruta del directorio a crear
	*@param int $mode el modo de permiso del directorio default es 0755 
	* @return $mkDir| regresa el directorio creado
    */
	public function creaDir($path, $mode = 0755)
	{	
		return @$mkDir = mkdir($path, $mode, true);
 	}
 
 	/**
    * Borra un directorio
	*@param string $path la ruta del directorio a borrar
	* @return $rmDir| regresa el dir borrado
    */
	public function borraDir($path)
 	{
		return @$rmDir = rmdir($path);
 	}
	
	/**
    * Verifica si un directorio existe
	*@param string $path la ruta del directorio a borrar
	* @return bool| regresa true si el dir existe en caso contrario false
    */
	public function existeDir($path)
	{
		return file_exists($path);
	}
}
?>