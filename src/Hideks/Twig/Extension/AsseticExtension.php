<?php

namespace Hideks\Twig\Extension;

use Hideks\Twig\TokenParser\AsseticTokenParser;
use Assetic\Factory\AssetFactory;

class AsseticExtension extends \Twig_Extension
{
    
    private $factory;

    public function __construct(AssetFactory $factory)
    {
        $this->factory = $factory;
    }
    
    public function getTokenParsers()
    {
        return array(
            new AsseticTokenParser($this->factory, 'javascripts', '*.js'),
            new AsseticTokenParser($this->factory, 'stylesheets', '*.css')
        );
    }
    
    public function getName()
    {
        return 'assetic_extension';
    }
    
}