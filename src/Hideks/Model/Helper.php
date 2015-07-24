<?php

namespace Hideks\Model;

use ActiveRecord\Model;

abstract class Helper extends Model
{
    
    public static function getSingleRow($filter)
    {
        $data = parent::find(self::getOptions($filter));
        
        if(empty($data)){ return false; }
        
        return $data->to_array();
    }
    
    public static function getMultiRows($filter, $limit = 10, $offset = 0)
    {
        $options = self::getOptions($filter);
        $options['limit'] = $limit;
        $options['offset'] = $offset;
        
        $dataArr = array();
        
        foreach(self::all($options) as $data){
            $dataArr[] = $data->to_array();
        }
        
        return $dataArr;
    }

    public static function getTotalRows($filter)
    {
        return self::count(self::getOptions($filter));
    }
    
    private static function getOptions($filter)
    {
        return self::reflector(self::parser($filter));
    }
    
    private static function parser($filter)
    {
        if( ($arr = explode('::', $filter)) && count($arr) === 1 ){
            $arr[1] = array();
        } else {
            $params = array();
            
            foreach(explode(',', $arr[1]) as $param){
                $param = explode(':', $param);
                
                if(count($param) < 2) {
                    continue;
                }
                
                $params[$param[0]] = $param[1]; 
            }
            
            $arr[1] = $params;
        }
        
        return $arr;
    }
    
    private static function reflector($method_and_params)
    {
        $class = get_called_class();
        
        $method = $method_and_params[0];
        
        $args = $method_and_params[1];
        
        $reflectionMethod = new \ReflectionMethod($class, $method);
        
        if(!$reflectionMethod->isStatic()){
            throw new \Exception("The method: $class::$method should be static!!");
        }
        
        if(!$reflectionMethod->isPrivate()){
            throw new \Exception("The static method: $class::$method should be private!!");
        }
        
        $params = array();
        
        foreach($reflectionMethod->getParameters() as $param){
            if(isset($args[$param->getName()])){
                $params[] = $args[$param->getName()];
            } else if($param->isOptional()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new \Exception("The method: $class::$method  signature requires a \"\${$param->getName()}\" argument!!");
            }
        }
        
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs(null, $params);
    }
    
}