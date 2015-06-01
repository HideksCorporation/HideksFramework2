<?php

namespace Hideks;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Container
{
    
    private static $instance = null;

    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = self::register();
        }
        
        return self::$instance;
    }
    
    public static function register()
    {
        $container = new ContainerBuilder();
        
        $container->register('context', 'Symfony\Component\Routing\RequestContext');
        
        $container->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')
            ->setArguments(array('%routes%', new Reference('context')));
        
        $container->register('resolver', 'Symfony\Component\HttpKernel\Controller\ControllerResolver');
        
        $container->register('generator', 'Symfony\Component\Routing\Generator\UrlGenerator')
            ->setArguments(array('%routes%', new Reference('context')));

        // Event Dispatcher
        $container->register('listener.router', 'Symfony\Component\HttpKernel\EventListener\RouterListener')
            ->setArguments(array(new Reference('matcher')));
        
        $container->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
            ->setArguments(array('UTF-8'));
        
        $container->register('listener.exception', 'Symfony\Component\HttpKernel\EventListener\ExceptionListener')
            ->setArguments(array('%exception.controller%'));
        
        $container->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
            ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
            ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));
        
        // Twig - Custom Extension
        $container->register('twig.extension.link_to', 'Hideks\Twig\Extension\LinkToExtension');
        
        // Twig - Template Engine
        $container->register('twig.loader', 'Twig_Loader_Filesystem')
            ->addMethodCall('addPath', array(__DIR__.'/View/templates', 'HideksFramework'));
        
        $container->register('twig', 'Twig_Environment')
            ->setArguments(array(new Reference('twig.loader'), array(
                'debug' => '%twig.debug%',
                'cache' => '%twig.cache%'
            )))
            ->addMethodCall('addExtension', array(new Reference('twig.extension.link_to')))
            ->addMethodCall('addExtension', array(new \Twig_Extension_Debug));
        
        return $container;
    }
    
}