<?php

namespace Hideks;

class View
{
    
    private $variables = array();
    
    public function __get($name)
    {
        return $this->variables[$name];
    }
    
    public function __set($name, $value)
    {
        $this->variables[$name] = $value;
    }
    
    public function getVariables()
    {
        return $this->variables;
    }
    
}