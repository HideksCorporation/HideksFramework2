<?php

namespace Hideks\Application;

use Hideks\Container;
use Symfony\Component\HttpKernel\HttpKernel;

class Bootstrap extends HttpKernel
{
    
    private $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
        
        parent::__construct(
            $this->container->get('dispatcher'),
            $this->container->get('resolver')
        );
    }
    
}