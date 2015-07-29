<?php

namespace Hideks\Twig\TokenParser;

use Hideks\Twig\Node\AsseticNode;
use Assetic\Factory\AssetFactory;

class AsseticTokenParser extends \Twig_TokenParser
{
    
    private $factory;
    private $tag;
    private $output;
    private $attributes = array();
    
    public function __construct(AssetFactory $factory, $tag, $output)
    {
        $this->factory = $factory;
        $this->tag     = $tag;
        $this->output  = DS.$tag.DS.$output;
    }

    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        
        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if($stream->test(\Twig_Token::NAME_TYPE, 'assets')){
                $this->parseAssets($stream);
            }
        }
        
        $this->attributes['debug'] = $this->factory->isDebug();
        
        $this->attributes['filters'] = $this->getFilters($this->attributes['debug']);
        
        $this->attributes['name'] = $this->getAssetName();
        
        $this->attributes['output'] = $this->output;
        
        $asset = $this->factory->createAsset($this->attributes['assets'], $this->attributes['filters'], $this->attributes);
        
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        
        $body = $this->parser->subparse(array($this, 'testEndTag'), true);
        
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        
        return new AsseticNode($asset, $body, $this->attributes, $token->getLine(), $this->getTag());
    }
    
    private function parseAssets($stream)
    {
        $stream->next();
        $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
        $stream->expect(\Twig_Token::PUNCTUATION_TYPE, '[');

        while ($stream->test(\Twig_Token::STRING_TYPE)) {
            $asset = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            
            $matches = array();
            
            if(!preg_match('/^require (.+)/', $asset, $matches)){
                throw new \Exception("You have a syntax error at: $asset");
            }
            
            $file = trim($matches[1]);
            
            $this->attributes['assets'][] = $this->getTag().DS.$file;
            
            if(!$stream->test(\Twig_Token::PUNCTUATION_TYPE, ',')){
                break;
            }

            $stream->next();
        }

        $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ']');
        
        return $stream;
    }

    public function getTag()
    {
        return $this->tag;
    }
    
    public function getFilters($isDebug)
    {
        $filters = array();
        
        if($this->getTag() === 'javascripts'){
            if($isDebug === false){
                $filters[] = 'js_min';
            }
        }
        
        if($this->getTag() === 'stylesheets'){
            if($isDebug === false){
                $filters[] = 'css_min';
            }

            $filters[] = 'lessphp';
        }
        
        return $filters;
    }
    
    public function getAssetName()
    {
        return $this->factory->generateAssetName($this->attributes['assets'], $this->attributes['filters'], $this->attributes);
    }

    public function testEndTag(\Twig_Token $token)
    {
        return $token->test(array('end'.$this->getTag()));
    }
    
}