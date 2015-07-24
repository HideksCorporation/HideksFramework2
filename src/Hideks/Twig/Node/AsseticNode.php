<?php

namespace Hideks\Twig\Node;

use Assetic\Asset\AssetInterface;

class AsseticNode extends \Twig_Node 
{
    
    public function __construct(AssetInterface $asset, \Twig_NodeInterface $body, $attributes, $lineno = 0, $tag = null)
    {
        $nodes = array('body' => $body);
        
        $attributes['asset']    = $asset;
        $attributes['var_name'] = 'asset_url';
        
        parent::__construct($nodes, $attributes, $lineno, $tag);
    }
    
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        
        $debug = $this->getAttribute('debug');
        
        $combine = true;
        
        if($debug){ $combine = false; }
        
        if($combine){
            $this->compileAsset($compiler, $this->getAttribute('asset'), $this->getAttribute('name'));
        } else {
            $this->compileDebug($compiler);
        }
        
        $compiler
            ->write('unset($context[')
            ->repr($this->getAttribute('var_name'))
            ->raw("]);\n");
    }

    private function compileDebug(\Twig_Compiler $compiler)
    {
        foreach($this->getAttribute('asset') as $asset){
            $this->compileAsset($compiler, $asset);
        }
    }

    private function compileAsset(\Twig_Compiler $compiler, AssetInterface $asset)
    {
        $compiler
            ->write('$context[')
            ->repr($this->getAttribute('var_name'))
            ->raw('] = ');
        
        $this->compileAssetUrl($compiler, $asset);
        
        $compiler
            ->raw(";\n")
            ->subcompile($this->getNode('body'));
    }
    
    private function compileAssetUrl(\Twig_Compiler $compiler, AssetInterface $asset)
    {
        $compiler->repr($asset->getTargetPath());
        
        if(file_exists(PUB_DIR.DS.$asset->getTargetPath())){
            return;
        }
        
        $writer = new \Assetic\AssetWriter(PUB_DIR.DS);
        $writer->writeAsset($asset);
    }
    
}