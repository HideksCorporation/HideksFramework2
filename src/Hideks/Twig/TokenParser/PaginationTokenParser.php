<?php

namespace Hideks\Twig\TokenParser;

use Hideks\Twig\Node\PaginationNode;

class PaginationTokenParser extends \Twig_TokenParser
{
    
    public function parse(\Twig_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        
        $stream = $this->parser->getStream();
        
        $type = $expr->getAttribute('value');
        
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        
        $expr->setAttribute('value', "@HideksFramework/fragments/$type-pagination.html.twig");
        
        return new PaginationNode($expr, $token->getLine(), $this->getTag());
    }
    
    public function getTag()
    {
        return 'pagination';
    }
    
}