<?php

namespace Hideks\Twig\Extension;

use Hideks\Container;

class LinkToExtension extends \Twig_Extension
{
    
    private $container;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    public function getFunctions()
    {
        return array(
            'linkTo' => new \Twig_Function_Method($this, 'linkTo')
        );
    }
    
    public function linkTo($name, array $parameters = array(), $absolute_path = false)
    {
        return $this->container->get('generator')->generate($name, $parameters, $absolute_path);
    }
    
    public function getName()
    {
        return 'linkto_extension';
    }
    
}