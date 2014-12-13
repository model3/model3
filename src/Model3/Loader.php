<?php
/**
* Clase Loader, para el M3
*
* Esta clase carga las clases de los archivos
* @package LIFE
* @author Hector Benitez
* @version 1.0
*/
class Model3_Loader
{
	/**
	* Carga las clases del modelo
	* @param string $class nombre de la clase
	* @param string $dirs directorios de la clase
	* @return
	*/
	public static function loadClass($class, $dirs = null)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        if ((null !== $dirs) && !is_string($dirs) && !is_array($dirs))
		{
            $code = self::_classException($class, "Error al cargar $class dir debe ser un arreglo o una cadena");
			eval($code);
			return;
        }

        // autodiscover the path from the class name

        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if (!empty($dirs)) {
            // use the autodiscovered path
            $dirPath = dirname($file);
            if (is_string($dirs)) {
                $dirs = explode(PATH_SEPARATOR, $dirs);
            }
            foreach ($dirs as $key => $dir) {
                if ($dir == '.') {
                    $dirs[$key] = $dirPath;
                } else {
                    $dir = rtrim($dir, '\\/');
                    $dirs[$key] = $dir . DIRECTORY_SEPARATOR . $dirPath;
                }
            }
            $file = basename($file);
            self::loadFile($file, $dirs, true);
        }
		else
		{
            if(!self::_securityCheck($file))
				return;

			if(!self::isReadable($file))
			{
                $code = self::_classException($class, "El archivo $file no existe");
				eval($code);
				return;
			}
			else
			{
				include $file;
			}
        }

        if (!class_exists($class, false) && !interface_exists($class, false))
		{
            $code = self::_classException($class, "La clase $class no existe en el archivo $file");
			eval($code);
			return;
        }
    }

	/**
	* Carga las archivos del modelo
	* @param string $filename nombre del archivo
	* @param bool $once incluye el include_once en los archivos
	* @return bool Regresa true si los archivos se cargaron correctamente , caso contrario false
	*/
	public static function loadFile($filename, $dirs = null, $once = false)
    {
        self::_securityCheck($filename);

        /**
         * Search in provided directories, as well as include_path
         */
        $incPath = false;
        if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }

        /**
         * Try finding for the plain filename in the include_path.
         */
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }

        /**
         * If searching in directories, reset include_path
         */
        if ($incPath) {
            set_include_path($incPath);
        }

        return true;
    }

	/**
	* Checa si el archivo se puede leer
	* @param string $filename nombre del archivo
	* @return bool Regresa true si el archivos se puede leer, caso contrario false
	*/
	public static function isReadable($filename)
    {
        if (!$fh = @fopen($filename, 'r', true))
		{
            return false;
        }
		fclose($fh);
        return true;
    }

	/**
	* Referencia a la clase estatica loadClass()
	* @param string $class nombre de la clase
	* @return $class Regresa la clase cargada en la clase estatica loadClass()
	*/
	public static function autoload($class)
    {
		self::loadClass($class);
        return $class;
    }

	/**
	* Registra el Loader
	* @param string $class nombre de la clase, default: Model3_Loader
	* @param bool $enabled habilita al loader
	*/
	public static function registerAutoload($class = 'Model3_Loader', $enabled = true)
    {
        if (!function_exists('spl_autoload_register')) {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception('spl_autoload does not exist in this PHP installation');
        }

        self::loadClass($class);
        $methods = get_class_methods($class);
        if (!in_array('autoload', (array) $methods)) {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception("The class \"$class\" does not have an autoload() method");
        }

        if ($enabled === true) {
            spl_autoload_register(array($class, 'autoload'));
        } else {
            spl_autoload_unregister(array($class, 'autoload'));
        }
    }

	/**
	* Checa si el archivo no tiene caracteres no permitidos
	* @param string $filename nombre del archivo
	* @return bool Regresa true si el archivo es correcto , caso contrario false
	*/
	protected static function _securityCheck($filename)
    {
        /**
        * Security check
        */
        if (preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename)) {
            return false;
        }
		return true;
    }

	/**
	* Incluye los archivos
	* @param string $filespec nombre del archivo
	* @param bool $once incluye el include_once en los archivos
	*/
	protected static function _includeFile($filespec, $once = false)
    {
        if ($once) {
            return include_once $filespec;
        } else {
            return include $filespec ;
        }
    }

	/**
	* Excepciones de la clase
	* @param string $class nombre de la clase
	* @return string $msg Mensaje de la excepcion
	*/
	protected static function _classException($class, $msg)
	{
		return "class $class
				{
					function __construct() {
						throw new Exception('$msg', 1001);
					}

					static function __callstatic(\$m, \$args) {
						throw new Exception('$msg', 1001);
					}

					function x_notaclass_x(){}
				}
				";
	}
}