<?php

namespace Hideks;

use Hideks\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller
{
    
    protected $container;
    protected $view;

    public function __construct()
    {
        $this->container = Container::getInstance();
        
        $this->view = new View();
    }
    
    public function renderTo($view)
    {
        $response = new Response();
        $response->setContent($this->container->get('twig')->render("$view.twig", $this->view->getVariables()));
        
        return $response;
    }
    public function linkTo($name, array $parameters = array(), $absolute = false)
    {
        return $this->container->get('generator')->generate($name, $parameters, $absolute);
    }
    
    public function redirectTo($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }
    
    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
    
}