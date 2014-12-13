<?php
/**
* Clase HtmlFactory, para el M3
*
* Esta clase crea elementos HTML 
* @package LIFE
* @author Octavio Cortés
* @version 1.0
*/

class Model3_HtmlFactory
{
	private $htmlElements;
	private $db;
	private $error;
	
	/**
	* Inserta elemento html
	* @param string $elementName nombre del elemento html
	* @param string $elementType tipo del elemento
	* @return int Regrea 1 si se inserto correctamente el elemento
	*/
	private function insertElement( $elementName, $elementType )
	{
		if ( !isset($elementName) || !isset($elementType) )
		{
			$this->error="El nombre o el tipo del elemento no fueron proporcionados";
			return 0;//alguno de los parametros no fue proporcionado o es NULL
		}
		$numElements = count ( $this->htmlElements );
		for ( $cont = 0 ; $cont < $numElements; $cont ++)
		{
			if ( $this->htmlElements[$cont][0] == $elementName )
				return -1; //indica que el elemento ya existe y no inserta el nuevo elemento
			$cont ++;
		}	
		$this->htmlElements[ $cont ][0] = $elementName;
		$this->htmlElements[ $cont ][1] = $elementType;
		return 1;//exito al insertar elemento
	}
	
	
	/**
	* Coloca la clase Db
	* @param $db la conexion de la bd
	* @return bool Regresa truesi la conexion tuvo exito en caso contrario false
	*/
	public function setDb ( $db )
	{
		if ( isset ( $db ) )
		{
			$this->db = $db;
			return true;
		}
		return false;
	}
	
	/**
	* Busca los elementos dado por el parametro $elementSearch
	* @param string $elementSearch elemento a buscar
	* @return $elementFound Regresa el elemento encontrado caso contrario NULL
	*/
	public function getElement( $elementToSearch )
	{
		if ( !isset($elementToSearch) )
			return NULL;
		
		$numElements = count($this->htmlElements);
		for ( $cont = 0; $cont < $numElements; $cont ++ )
		{
			if ( $this->htmlElements[$cont][0] == $elementToSearch )
			{
				$elementFound[0] = $htmlElements[$cont][0];//nombre del elemento
				$elementFound[1] = $htmlElements[$cont][1];//tipo del elemento
			}
		}
		return isset($elementFound) ? $elementFound : NULL;
	}
	
	/**
	* Construye sun combobox.
	* @param string $name el nombre del combobox
	* @param string $class la clase del estilo
	* @param array $options las opciones del combobox
	* @param string $optionSelected el valor seleccionado por default
	* @return $res El combobox con sus opciones
	*/
	public function setSelect ($name, $class, $options, $optionSelected = NULL)
	{
		//se verifica que todos los elementos existan a excepcion de $selectedOption que puede ser NULL
		if ( !isset($name) || !isset($options) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setSelect\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "select" ) ) == 1 )
		{
			if($class != NULL)
				echo "<select name=\"$name\" id=\"$name\" class=\"$class\">";
			else
				echo "<select name=\"$name\" id=\"$name\" >";
			foreach ( $options as $option )
			{
				if( $option[0] == $optionSelected )
					echo "<option value=\"$option[0]\" selected>$option[1]</option>";
				else
					echo "<option value=\"$option[0]\">$option[1]</option>";
				$cont ++ ;
			}						
			echo "</select>";
		}
		return $res;
	}
	
	
	/**
	* Contruye un conjunto de comboboxes con fechas
	* @param string $dia el nombre del combobox donde se van a visualizar los dias
	* @param string $mes el nombre del combobox donode se van a visualizar los meses
	* @param string $anio el nombre del combobox donode se van a visualizar los meses
	* @param string $fecha No se usa.
	* @return Regresa el conjunto de comboboxes creados
	*/
	public function setDateSelect($dia, $mes, $anio, $fecha = '')
	{
		$dias = array();
		$meses = array();
		$anios = array();
		
		for($idx=1;$idx<=31;$idx++)
			$dias[] = array($idx, $idx);
		
		for($idx=1;$idx<=12;$idx++)
			$meses[] = array($idx, $idx);
			
		for($idx=1950;$idx<=2009;$idx++)
			$anios[] = array($idx, $idx);
			
		$this->setSelect($dia, '', $dias);
		echo ' ';
		$this->setSelect($mes, '', $meses);
		echo ' ';
		$this->setSelect($anio, '', $anios, 1980);
	}
	
	/**
	* Contruye un <input type='text'>
	* @param string $name el nombre del elemento
	* @param string $value el valor del elemento
	* @param string $size el tamaño del elemento default es 2
	* @return $res | Regresa el elemento creado
	*/
	public function setTextBox ( $name, $value, $size = 2)
	{
		//se verifica que todos los elementos existan a excepcion de $selectedValue que puede ser NULL
		if ( !isset($name) || !isset($value) )
		{
			$this->error="Alguno de los elementos proporcionados a <b>\"setTextBox\"</b> es NULL";
			return false;
		}
		//checa si no hay otro elemento con el mismo nombre y en caso de que no haya, inserta el nuevo elemento
		if ( ( $res = $this->insertElement( $name, "text" ) ) == 1 )
			echo "<input name=\"$name\" type=\"text\" id=\"$name\" size=\"$size\" value=\"$value\"/>";
		return $res;			
	}
	
	/**
	* Contruye un <input type='checkbox'>
	* @param string $name el nombre del elemento
	* @param string $checked la propiedad checked del checkbox; Valor por default false
	* @return $res | Regresa el elemento creado
	*/
	public function setCheckBox ( $name, $checked = false )
	{
		//se verifica que todos los elementos existan a excepcion de $selectedValue que puede ser NULL
		if ( !isset($name) || !isset($checked) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setTextBox\"</b> es NULL";
			return false;
		}
		//checa si no hay otro elemento con el mismo nombre y en caso de que no haya, inserta el nuevo elemento
		if ( ( $res = $this->insertElement( $name, "checkbox" ) ) == 1 )
			if($checked)
				echo "<input name=\"$name\" type=\"checkbox\" id=\"$name\" checked/>";
			else
				echo "<input name=\"$name\" type=\"checkbox\" id=\"$name\"/>";
		return $res;
	}
	
	/**
	* Contruye un conjunto de <input type='radio'>
	* @param string $name el nombre del elemento
	* @param int $mode checa si es un solo elemento o varios
	* @param string $optionSelected la propiedad checked del elemento; Valor por default NULL
	* @return $res | Regresa el elemento creado
	*/
	public function setRadio ( $name, $options, $mode = 0 , $optionSelected = NULL )
	{
		//se verifica que todos los elementos existan a excepcion de $selectedOption que puede ser NULL
		if ( !isset($name) || !isset($options) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setRadio\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "radio" ) ) == 1 )
		{
			foreach ( $options as $option )
			{
				if( $option[0] == $optionSelected )
					echo "<input type=\"radio\" name=\"$name\" value=\"$option[0]\" id=\"$name\" checked=\"checked\"/>";
				else
					echo "<input type=\"radio\" name=\"$name\" value=\"$option[0]\" id=\"$name\"/>";
				if ( $mode == 0 )
					echo $option[1]."  ";
				else
					echo $option[1]."<br>";
			}						
			echo "</select>";
		}
		return $res;
	}
	
	/**
	* Contruye un <input type='submit'>
	* @param string $name el nombre del elemento
	* @param string $value el valor del elemento
	* @return $res | Regresa el elemento creado
	*/
	public function setSubmit ( $name, $value )
	{
		//se verifica que todos los elementos existan 
		if ( !isset($name) || !isset($value) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setRadio\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "submit" ) ) == 1 )
		{
			echo "<input type=\"submit\" name=\"$name\" id=\"$name\" value=\"$value\" />";
		}
		return $res;
	}

	/**
	* Contruye un <input type='button>
	* @param string $name el nombre del elemento
	* @param string $value el valor del elemento
	* @return $res | Regresa el elemento creado
	*/
	public function setButton ( $name, $value )
	{
		if ( !isset($name) || !isset($value) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setRadio\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "button" ) ) == 1 )
		{
			echo "<input type=\"button\" name=\"$name\" id=\"$name\" value=\"$value\" />";
		}
		return $res;
	}
	
	/**
	* Contruye un loginBox
	* @param string $formAction la url del formulario
	* @param string $value el valor del elemento
	* @return $res | Regresa el elemento creado
	*/
	public function loginBox($formAction)
	{
		echo '<div id="divLogin" class="block">
                <form action="'.$formAction.'" method="post" >
                <table align="center">
                	<tr>
                		<td>Nombre de usuario:</td>
                		<td><input type="text" name="username" /></td>
                	</tr>
                	<tr>
                    	<td>Password: </td>
                    	<td><input type="password" name="password" /></td>
                    </tr>
                    <tr>
                    	<td colspan="2"><input type="submit" value="Entrar" /></td>	
                	</tr>                    
                </table>
                </form>
            </div>';
	}
	
	
	/**
	* Contruye un tabla con celdas de diferente color (even,odd)
	* @param string $headers el header de la tabla
	* @param string $data los datos de la tabla
	*/
	public function zebraTable($headers, $data)
	{
		$cols = count($headers);
		echo '<table class="zebraTable" ><tr>';
		foreach($headers as $colHeader)
			echo '<th  width="'.$colHeader['ancho'].'" style="text-align:'.$colHeader['align'].'" >'.$colHeader['titulo'].'</th>';
		echo '</tr></table>';
		
		echo '<table class="zebraTable" >';
		$rows = count($data);
		for( $contRow = 0; $contRow < $rows; $contRow++ )
		{
			echo '<tr class='.($contRow % 2 ? 'rowEven' : 'rowOdd').'>';
			$contCol = 0;
			foreach($data[$contRow] as $cell)
			{
				echo '<td  width="'.$headers[$contCol]['ancho'].'" style="text-align:'.$headers[$contCol]['align'].'" >'.$cell.'</td>';
				$contCol = ($contCol+1)%$cols;
			}
			echo '</tr>';
		}
		echo '</tr></table>';
	}
	
	/**
	* Contruye un paginador de datos
	* @param int $current el elemento actual
	* @param int $total el total de elementos
	* @param string $link  el link de los elementos
	*/
	public function paginatorControl($current, $total, $link)
	{
		if($current > 1)
			echo '<a href="'.$link.($current-1).'" >Anterior</a>&nbsp;';
		for($idx = 1;$idx <= $total; $idx++)
		{
			if($idx == $current)
				echo $idx.'&nbsp;';
			else
				echo '<a href="'.$link.$idx.'" >'.$idx.'</a>&nbsp;';
		}
		if($current < $total)
			echo '<a href="'.$link.($current+1).'" >Siguiente</a>';
	}
}