<?php

namespace Hideks;

class Paginator
{
    
    private $totalItens;
    private $limitPerPage;
    private $offset;
    private $limit;
    private $attributes = array();
    private $routes     = array();
    private $params     = array();

    public function __construct(array $args)
    {
        if (!isset($args['totalItens'])) {
            throw new \Exception('The configuration array should have a "totalItens" index');
        }
        
        if ((is_integer($args['totalItens']) and $args['totalItens'] > 0) === false) {
            throw new Exception('The configuration array index "totalItens" must be an integer and greater than 0');
        }
        
        $this->currentPage  = isset($args['currentPage'])  ? $args['currentPage'] : 1;
        
        $this->limitPerPage = isset($args['limitPerPage']) ? $args['limitPerPage'] : 10;

        $this->totalItens   = $args['totalItens'];
        
        $this->routes = array(
            str_replace('_pagination', '', $args['route']),
            $args['route']
        );
        
        $this->params = isset($args['params']) ? $args['params'] : array();
    }
    
    public function __get($name) {
        return $this->attributes[$name];
    }
    
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    public function getOffset() {
        return $this->offset;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function paginate()
    {
        $this->calculate();
        
        $this->previous = $this->createPrevUrl($this->previousPage);
                
        $this->first = $this->createFirstUrl($this->firstPage);
        
        $pages = array();
        
        for($i = $this->before; $i <= $this->after; $i++){
            if($this->previousPage === $i){
                $pages[] = $this->createPrevUrl($i);
            } elseif($this->nextPage === $i) {
                $pages[] = $this->createNextUrl($i);
            } elseif($this->firstPage === $i) {
                $pages[] = $this->createFirstUrl($i);
            } else {
                $pages[] = $this->createDefaultUrl($i);
            }
        }
        
        $this->pages = $pages;
        
        $this->last = $this->createLastUrl($this->lastPage);
                
        $this->next = $this->createNextUrl($this->nextPage);
        
        return $this->attributes;
    }
    
    private function calculate()
    {
        $this->totalPages = (int) ceil($this->totalItens / $this->limitPerPage);

        $this->currentPage = (int) min(max(1, $this->currentPage), max(1, $this->totalPages));

        $this->firstItem = (int) min((($this->currentPage - 1) * $this->limitPerPage) + 1, $this->totalItens);

        $this->lastItem = (int) min($this->firstItem + $this->limitPerPage - 1, $this->totalItens);

        $this->previousPage = ($this->currentPage > 1) ? $this->currentPage - 1 : false;

        $this->nextPage = ($this->currentPage < $this->totalPages) ? $this->currentPage + 1 : false;

        $this->firstPage = ($this->currentPage === 1) ? false : 1;

        $this->lastPage = ($this->currentPage >= $this->totalPages) ? false : $this->totalPages;

        $this->before = (($this->currentPage - 4) < 1) ? 1 : $this->currentPage - 4;

        $this->after = (($this->currentPage + 4) > $this->totalPages) ? $this->totalPages : $this->currentPage + 4;

        $this->offset = (int) (($this->currentPage - 1) * $this->limitPerPage);

        $this->limit = $this->limitPerPage;
    }
    
    private function isSecondPage()
    {
        return $this->currentPage === 2;
    }
    
    private function createPrevUrl($number)
    {
        if($this->isSecondPage()){
            return $this->createUrl($number, 'start prev', true);
        } else {
            return $this->createUrl($number, 'prev', false);
        }
    }
    
    private function createNextUrl($number)
    {
        return $this->createUrl($number, 'next', false);
    }

    private function createFirstUrl($number)
    {
        return $this->createUrl($number, 'start', true);
    }
    
    private function createLastUrl($number)
    {
        if(($this->currentPage + 1) === $this->lastPage){
            return $this->createUrl($number, 'next', true);
        } else {
            return $this->createDefaultUrl($number);
        }
    }
    
    private function createDefaultUrl($number)
    {
        return $this->createUrl($number, false, false);
    }
    
    private function createUrl($number, $rel, $isRoot)
    {
        if($isRoot){
            return array(
                'route'  => $this->routes[0],
                'params' => $this->params,
                'number' => $number,
                'rel'    => $rel
            );
        } else {
            return array(
                'route'  => $this->routes[1],
                'params' => array_merge($this->params, array(
                    'page' => $number
                )),
                'number' => $number,
                'rel'    => $rel
            );
        }
    }
    
}