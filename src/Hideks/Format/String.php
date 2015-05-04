<?php

namespace Hideks\Format;

class String {
    
    private $string = null;

    public function __construct($string) {
        $this->string = $string;
    }

    public function toPreventInjection() {
        $this->string = (get_magic_quotes_gpc() === 0) ? addslashes($this->string) : $this->string;

        $this->string = trim($this->string);

        $this->string = strip_tags($this->string);

        return $this->string;
    }
    
    public function toSeo() {
        $this->string = strtr($this->string,"ÁÃÂÀÄÉÊÈËÍÎÌÏÓÕÔÒÖÚÛÙÜÑÇ","áãâàäéêèëíîìïóõôòöúûùüñç");
        $this->string = utf8_encode($this->string);
	$this->string = preg_replace("[Ã¡|Ã£|Ã¢|Ã |Ã¥|Ã¤] i", "a", $this->string);
	$this->string = preg_replace("[Ã©|Ãª|Ã¨|Ã«] i", "e", $this->string);
	$this->string = preg_replace("[Ã­|Ã¬|Ã®|Ã¯] i", "i", $this->string);
	$this->string = preg_replace("[Ã³|Ãµ|Ã´|Ã²|Ã¶] i", "o", $this->string);
	$this->string = preg_replace("[Ãº|Ã»|Ã¹|Ã¼] i", "u", $this->string);
	$this->string = preg_replace("[Ã±] i", "n", $this->string);
	$this->string = preg_replace("[Ã§] i", "c", $this->string);
	$this->string = preg_replace("`\[.*\]`U", "", $this->string);
	$this->string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $this->string);
	$this->string = preg_replace("/\d/", "", $this->string);
        $this->string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $this->string);
	$this->string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $this->string);
        return strtolower(trim($this->string, '-'));
    }
    
}