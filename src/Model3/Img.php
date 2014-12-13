<?php
/**
* Clase para redimensionar la imagen del usuario
*
* Esta clase especifica unas funciones para redimensionar una imagen JPG,GIF o PNG, basada en una clase hecha por Chris Lopez
* de Logistic-Apps
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyleft 2009
*/

/**
* Clase Images, esta clase controlara las funciones para redimensionar imagenes
* @package VoIP-Life
* @subpackage General
* @author Hector Benitez
* @version 1.0
* @copyright 2009
*/
ini_set("memory_limit","80M");
class Model3_Img{
	
	/**
	* variables que contienen el nombre de la imagen , el directorio donde se encuentra 
	* y el nuevo nombre que se le da despues de redimensionarla
	*/
	private $imagen;
	private $nombre_imagen_asociada;
	private $directorio;
	
	/**
	* constructor de la clase
	* <code>
	* <?php
	* $images = new Images('$nom_imagen);
	* ?>
	* </code>
	* @param string $nom_imagen Nombre de la imagen con su directorio
	* @param string $dirImagen el directorio donde se va guardar la nueva imagen
	*/	
	public function __construct($image){
		$this->imagen = $image;
		$this->nombre_imagen_asociada = basename($image);		
	}
	
	/**
	* Este metodo recibe tres parametros ancho y alto para acomdarse a diferentes tamaños de imagenes, ademas de el 
	* nombre de la imagen que resulta...
	*/
	public function redimensionarImagen($ancho, $alto, $imagen)
	{
		if(($ancho == 0)&&($alto == 0))
		{
			return false;
		}
		
		$nombre_imagen_asociada = $imagen;

		$nuevo_ancho = $ancho;
        $nuevo_alto = $alto;
		$info_imagen = getimagesize($this->imagen);
        $alto = $info_imagen[1];
        $ancho = $info_imagen[0];
        $tipo_imagen = $info_imagen[2];
		
		if($nuevo_alto == 0)
		{
			$nuevo_alto = $alto;
		}
		
		if($nuevo_ancho == 0)
		{
			$nuevo_ancho = $ancho;
		}
		
		if($ancho > $nuevo_ancho OR $alto > $nuevo_alto)
        {	
            if(($alto - $nuevo_alto) > ($ancho - $nuevo_ancho))
            {
                $nuevo_ancho = round($ancho * $nuevo_alto / $alto,0) ;       
            }
            else
            {
               $nuevo_alto = round($alto * $nuevo_ancho / $ancho,0);   
            }
		
		}
        else /**si la imagen es más pequeña que los límites la dejo igual.*/		 
        {
            $nuevo_alto = $alto;
            $nuevo_ancho = $ancho;
        }
        /*
		* dependiendo del tipo de imagen tengo que usar diferentes funciones
		*/
		switch ($tipo_imagen) {
            case 1: //si es gif …
                $imagen_nueva = imagecreate($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromgif($this->imagen);
               //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagegif($imagen_nueva, $nombre_imagen_asociada)) return false;#
            	break;

			case 2: //si es jpeg …
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromjpeg($this->imagen);
                //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagejpeg($imagen_nueva, $nombre_imagen_asociada)) return false;
            	break;

			case 3: //si es png …
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefrompng($this->imagen);
                //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
               	if (!imagepng($imagen_nueva, $nombre_imagen_asociada)) return false;
           		break;
        }
       return true;
	}
	// permite redimensionar a medidas grandes
	public function redimensionarImagen2($ancho, $alto, $imagen)
	{
		if(($ancho == 0)&&($alto == 0))
		{
			return false;
		}
		
		$nombre_imagen_asociada = $imagen;				
		$info_imagen = getimagesize($this->imagen);		
		$aux = $ancho/$info_imagen[0];
        $alto = $info_imagen[1];
        $ancho = $info_imagen[0];		
		$nuevo_ancho = $ancho * $aux;
        $nuevo_alto = $alto * $aux;
        $tipo_imagen = $info_imagen[2];
		
		if($nuevo_alto == 0)
		{
			$nuevo_alto = $alto;
		}
		
		if($nuevo_ancho == 0)
		{
			$nuevo_ancho = $ancho;
		}
		
		/*
		* dependiendo del tipo de imagen tengo que usar diferentes funciones
		*/
		switch ($tipo_imagen) {
            case 1: //si es gif …
                $imagen_nueva = imagecreate($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromgif($this->imagen);
               //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagegif($imagen_nueva, $nombre_imagen_asociada)) return false;#
            	break;

			case 2: //si es jpeg …
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromjpeg($this->imagen);
                //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagejpeg($imagen_nueva, $nombre_imagen_asociada)) return false;
            	break;

			case 3: //si es png …
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefrompng($this->imagen);
                //cambio de tamaño…
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
               	if (!imagepng($imagen_nueva, $nombre_imagen_asociada)) return false;
           		break;
        }
       return true;
	}
}
?>