<?php

namespace Model3\View;

class HtmlFactory
{
	private $htmlElements;
	private $db;
	private $error;
	
	/**
	*
	* @param string $elementName
	* @param string $elementType
	* @return int
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
	*
	* @param $db
	* @return bool
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
	 *
	 * @param $elementToSearch
	 * @internal param string $elementSearch
	 * @return $elementFound
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
	 *
	 * @param string $name
	 * @param string $class
	 * @param array $options
	 * @param string $optionSelected
	 * @return bool|int $res
	 */
	public function setSelect ($name, $class, $options, $optionSelected = NULL)
	{
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
	*
	* @param string $dia
	* @param string $mes
	* @param string $anio
	* @param string $fecha
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
	
	public function setTextBox ( $name, $value, $size = 2)
	{
		if ( !isset($name) || !isset($value) )
		{
			$this->error="Alguno de los elementos proporcionados a <b>\"setTextBox\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "text" ) ) == 1 )
			echo "<input name=\"$name\" type=\"text\" id=\"$name\" size=\"$size\" value=\"$value\"/>";
		return $res;			
	}
	
	public function setCheckBox ( $name, $checked = false )
	{
		if ( !isset($name) || !isset($checked) )
		{
			echo "Alguno de los elementos proporcionados a <b>\"setTextBox\"</b> es NULL";
			return false;
		}
		if ( ( $res = $this->insertElement( $name, "checkbox" ) ) == 1 )
			if($checked)
				echo "<input name=\"$name\" type=\"checkbox\" id=\"$name\" checked/>";
			else
				echo "<input name=\"$name\" type=\"checkbox\" id=\"$name\"/>";
		return $res;
	}
	
	public function setRadio ( $name, $options, $mode = 0 , $optionSelected = NULL )
	{
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