<?php

namespace Hideks\Controller;

use Hideks\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;

class ErrorController extends Controller
{
    
    public function exceptionAction(FlattenException $exception)
    {
        return $this->renderTo('@HideksFramework/exception', array(
            'status_code' => $exception->getStatusCode(),
            'message'     => $exception->getMessage(),
            'file'        => $this->getShortFileName($exception->getFile()),
            'line'        => $exception->getLine(),
            'trace'       => $this->parseTrace($exception->getTrace())
        ), new Response());
    }
    
    private function parseTrace($trace)
    {
        $routes = array();
        
        foreach ($trace as $route) {
            $route['args'] = $this->parseArgs($route['args']);
            
            $routes[] = array(
                'file' => $this->getShortFileName($route['file']),
                'line' => $route['line'],
                'function' => "{$route['class']}{$route['type']}{$route['function']}({$route['args']})"
            );
        }
        
        array_shift($routes);
        
        return $routes;
    }
    
    private function parseArgs($args)
    {
        if(empty($args)){
            return '';
        }
        
        $params = array();
        
        foreach($args as $arg){
            $params[] = $this->parseRecursive($arg);
        }
        
        return implode(', ', $params);
    }
    
    private function parseRecursive($args)
    {
        if($args[0] !== 'array'){
            return $args[1];
        }
        
        if($args[0] === 'array'){
            $params = array();
            
            foreach($args[1] as $arg){
                $params[] = $this->parseRecursive($arg);
            }
            
            return 'array('.implode(', ', $params).')';
        }
        
    }
    
    private function getShortFileName($fileName)
    {
        if( ($string = strpos($fileName, $this->getProjectName())) ){
            return substr($fileName, $string);
        }
    }
    
    private function getProjectName()
    {
        $parts = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['DOCUMENT_ROOT']));
        
        $last = count($parts) - 1;
        
        return $parts[$last];
    }
    
}