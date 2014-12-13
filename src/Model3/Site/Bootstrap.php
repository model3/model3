<?php
/**
 * Clase Bootstrap del M3
 *
 * Esta clase simplemente ayuda al arranque
 * @author Hector Benitez
 * @version 1.0
 * @copyright 2008 Hector Benitez
 */
class Model3_Site_Bootstrap
{
    public function init()
    {
        $methodNames = get_class_methods($this);
        foreach ($methodNames as $method)
        {
            if (5 < strlen($method) && '_init' === substr($method, 0, 5))
            {
                $this->$method();
            }
        }
    }
}