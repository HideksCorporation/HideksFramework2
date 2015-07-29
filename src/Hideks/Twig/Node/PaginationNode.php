<?php

namespace Hideks\Twig\Node;

class PaginationNode extends \Twig_Node
{
    
    public function __construct(\Twig_Node_Expression $expr, $lineno = 0, $tag = null)
    {
        parent::__construct(array(
            'expr' => $expr
        ), array(), $lineno, $tag);
    }
    
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        
        $compiler
            ->write("try {\n")
            ->indent()
        ;
        
        $this->addGetTemplate($compiler);
        
        $compiler
            ->outdent()
            ->write("} catch (Twig_Error_Loader \$e) {\n")
            ->indent()
            ->write("throw new \Exception(\$e);\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
    
    protected function addGetTemplate(\Twig_Compiler $compiler)
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->subcompile($this->getNode('expr'))
            ->raw(', ')
            ->repr($compiler->getFilename())
            ->raw(', ')
            ->repr($this->getLine())
            ->raw(')')
            ->raw('->display(')
            ->raw('$context')
            ->raw(");\n")
        ;
    }

}