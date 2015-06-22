<?php

namespace Hideks;

use ActiveRecord\Model;

class Format {
    
    public static function string($string) {
        if( !is_string($string) ){
            throw new \Exception('The argument passed to string() must be a string!!');
        }
        
        return new Format\Text($string);
    }
    
    public static function objects($objects) {
        if( !is_array($objects) ){
            throw new \Exception('The argument passed to objects() must be an array of \ActiveRecord\Model objects!!');
        }
        
        foreach ($objects as $object) {
            if ( !$object instanceof Model ) {
                throw new \Exception("The objects array passed to objects() must be an instance of \ActiveRecord\Model!!");
            }
        }
        
        return new Format\Objects($objects);
    }
    
}