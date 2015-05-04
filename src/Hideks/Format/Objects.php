<?php

namespace Hideks\Format;

class Objects {
    
    private $objects;
    
    public function __construct($objects) {
        $this->objects = $objects;
    }
    
    public function toStringByColumn($column) {
        $count = count($this->objects);
        
        if( $count <= 0 ){
            return '';
        }
        
        if( $count > 2 ){
            $names = array();
            
            for($i = 0; $i <= $count -2; $i++){
                $names[] = $this->objects[$i]->$column;
            }
            
            $names = implode(', ', $names);
            
            return $names.' e '.$this->objects[$count - 1]->$column;
        }
        
        if( $count > 1 ){
            return $this->objects[0]->$column.' e '.$this->objects[1]->$column;
        } else {
            return $this->objects[0]->$column;
        }
    }
    
}