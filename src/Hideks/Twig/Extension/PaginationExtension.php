<?php

namespace Hideks\Twig\Extension;

use Hideks\Twig\TokenParser\PaginationTokenParser;

class PaginationExtension extends \Twig_Extension
{
    
    public function getTokenParsers()
    {
        return array(
            new PaginationTokenParser()
        );
    }
    
    public function getName()
    {
        return 'pagination_extension';
    }
    
}