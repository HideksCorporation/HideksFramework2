<?php

namespace Hideks;

use Hideks\Container;
use Hideks\Application\Bootstrap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Yaml\Parser;
use Composer\Autoload\ClassLoader;

class Application
{
    private $request;

    public function __construct()
    {
        defined('DS')
            || define('DS', DIRECTORY_SEPARATOR);
        
        defined('APP_DIR')
            || define('APP_DIR', dirname(dirname(__DIR__)).DS.'application');
        
        $this->request = Request::createFromGlobals();
        
        $container = Container::getInstance();
        
        /* Parse params file - Begin */
        $params = $this->parseYamlFile(APP_DIR.DS.'configs'.DS.'params.yml');
        
        $isDev = $params['environment'] === 'development';
        
        if($isDev){ Debug::enable(); }
        
        date_default_timezone_set($params['timezone']);
        
        $container->setParameter('params', $params);
        /* Parse params file - End */
        
        /* Parse routes file - Begin */
        $routes = $this->parseYamlFile(APP_DIR.DS.'configs'.DS.'routes.yml');
        
        $collection = new RouteCollection();
        
        foreach($routes as $name => $options){
            $route = new Route($options['path']);
            $route->setDefaults(isset($options['defaults']) ? $options['defaults'] : array());
            $route->setRequirements(isset($options['requirements']) ? $options['requirements'] : array());
            $route->setOptions(isset($options['options']) ? $options['options'] : array());
            $route->setHost(isset($options['host']) ? $options['host'] : '');
            $route->setSchemes(isset($options['schemes']) ? $options['schemes'] : array());
            $route->setMethods(isset($options['methods']) ? $options['methods'] : array());
            $route->setCondition(isset($options['condition']) ? $options['condition'] : '');
            
            $collection->add($name, $route);
        }
        
        $container->setParameter('routes', $collection);
        /* Parse routes file - End */
        
        /* Composer ClassLoader - Begin */
        $composer_loader = new ClassLoader();
        $composer_loader->addPsr4($params['namespace'].'\\', APP_DIR.DS.'layers');
        $composer_loader->register();
        /* Composer ClassLoader - End */
        
        /* Set error controller - Begin */
        $namespace = $isDev ? 'Hideks\\Controller\\' : $params['namespace'].'\\Controllers\\';
        
        $container->setParameter('exception.controller', $namespace.'ErrorController::exceptionAction');
        /* Set error controller - End */
        
        /* Twig configuration setup - Begin */
        $container->setParameter('twig.debug', $isDev);
        $container->setParameter('twig.cache', $isDev ? false : APP_DIR.DS.'cache');
        
        $twig_loader = $container->get('twig.loader');
        $twig_loader->addPath(APP_DIR.DS.'layers'.DS.'views');
        /* Twig configuration setup - End */
        
        /* Active Record configuration setup - Begin */
        $active_record = \ActiveRecord\Config::instance();
        $active_record->set_model_directory(APP_DIR.DS.'layers'.DS.'models');
        $active_record->set_connections($params['connections']);
        $active_record->set_default_connection($params['environment']);
        /* Active Record configuration setup - End */
    }
    
    public function parseYamlFile($filepath)
    {
        $yaml = new Parser();
        
        return $yaml->parse(file_get_contents($filepath));
    }
    
    public function run()
    {
        $front_controller = new Bootstrap();
        $front_controller
                ->handle($this->request)
                ->send();
    }
    
}