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
use Assetic\FilterManager;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\JSMinFilter;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\CacheBustingWorker;

class Application
{
    
    private $request;
    private $container;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        
        $this->container = Container::getInstance();
        
        /* Parse params file - Begin */
        $params = $this->parseYamlFile(APP_DIR.DS.'configs'.DS.'params.yml');
        
        $isDev = $params['environment'] === 'development';
        
        if($isDev){ Debug::enable(E_STRICT); }
        
        date_default_timezone_set($params['timezone']);
        /* Parse params file - End */
        
        /* Parse routes file - Begin */
        $routes = $this->parseYamlFile(APP_DIR.DS.'configs'.DS.'routes.yml');
        
        $collection = new RouteCollection();
        
        foreach($routes as $name => $options){
            $parts = explode(':',$options['defaults']['_controller']);
            
            $options['defaults'] = array(
                '_controller' => "{$parts[0]}\\Controllers\\{$parts[1]}Controller::{$parts[2]}Action"
            );
            
            $route = new Route($options['path']);
            $route->setDefaults($options['defaults']);
            $route->setRequirements(isset($options['requirements']) ? $options['requirements'] : array());
            $route->setOptions(isset($options['options']) ? $options['options'] : array());
            $route->setHost(isset($options['host']) ? $options['host'] : '');
            $route->setSchemes(isset($options['schemes']) ? $options['schemes'] : array());
            $route->setMethods(isset($options['methods']) ? $options['methods'] : array());
            $route->setCondition(isset($options['condition']) ? $options['condition'] : '');
            
            $collection->add($name, $route);
        }
        
        $this->container->setParameter('routes', $collection);
        /* Parse routes file - End */
        
        /* Composer ClassLoader - Begin */
        $composer_loader = new ClassLoader();
        $composer_loader->addPsr4('Application\\Controllers\\', APP_DIR.DS.'layers'.DS.'controllers');
        $composer_loader->addPsr4('Application\\Models\\', APP_DIR.DS.'layers'.DS.'models');
        $composer_loader->register();
        /* Composer ClassLoader - End */
        
        /* Set error controller - Begin */
        $namespace = $isDev ? 'Hideks\\Controller\\' : 'Application\\Controllers\\';
        
        $this->container->setParameter('exception.controller', $namespace.'ErrorController::exceptionAction');
        /* Set error controller - End */
        
        /* Assetic configuration setup - Begin */
        $filter_manager = new FilterManager();
        $filter_manager->set('css_min', new CssMinFilter());
        $filter_manager->set('lessphp', new LessphpFilter());
        $filter_manager->set('js_min', new JSMinFilter());
        
        $asset_factory = new AssetFactory(APP_DIR.DS.'assets'.DS);
        $asset_factory->setDebug($isDev);
        $asset_factory->setFilterManager($filter_manager);
        $asset_factory->addWorker(new CacheBustingWorker());
        
        $this->container->setParameter('assetic.factory', $asset_factory);
        /* Assetic configuration setup - End */
        
        /* Twig configuration setup - Begin */
        $this->container->setParameter('twig.debug', $isDev);
        $this->container->setParameter('twig.cache', $isDev ? false : APP_DIR.DS.'cache'.DS.'twig');
        
        $twig_loader = $this->container->get('twig.loader');
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