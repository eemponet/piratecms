<?php

namespace App\Controllers;

class Page extends ControllerApp{
	
	// public $translate = true;
	// protected $models = array("Pages","Areas","Noticias","Definicoes","Traducoes");
	
	public $title = '';
	
	function index()
	{
		
	}
	
	function lang()
	{ //muda de lingua, na pagina em que esta!!
		$lang = $this->f3->get('PARAMS.lang');
		$new_lang = $this->f3->get('PARAMS.p1');
		
        $new_lang_url = '/'.$new_lang.substr($this->f3->get('SESSION.lastroute'),3);
        
        $this->f3->reroute($new_lang_url);
        
    }
	
}
