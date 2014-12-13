<?php

namespace Model3\Site;

class Bootstrap
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