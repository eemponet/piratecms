<?php

namespace App\Controllers;

class Page extends ControllerApp{
	
	// public $translate = true;
	protected $models = array("Configs","Members");
	
	public $title = '';
	
	// public $layout ='page';
	// function __construct() {
	// 	parent::__construct();
		
	// 	$this->layout = 'page';
	// }
	function index()
	{
		
	}
	
	function show()
	{
		$this->f3->set('hide_banner',1);
		$page_id = $this->f3->get('PARAMS.p1');
		$page = $this->Configs->getPage($page_id);
		if(empty($page)){
			$this->error('página não encontrada!');
			$this->f3->reroute('/');
		}
		$this->title = $page['value_2'];
		$this->f3->set('page',$page);
		
		
		$this->description = substr(strip_tags($page['value_1']),0,155);
	}
	
	function lang()
	{ //muda de lingua, na pagina em que esta!!
		$lang = $this->f3->get('PARAMS.lang');
		$new_lang = $this->f3->get('PARAMS.p1');
		
        $new_lang_url = '/'.$new_lang.substr($this->f3->get('SESSION.lastroute'),3);
        
        $this->f3->reroute('/'.$new_lang);
        
    }
    
    function newpage()
    {
    	$menus = array(1 => 1,2 => 2,3 => 3);
    	$this->f3->set('menus',$menus);
    	if($this->Members->isAuthenticated()){
    		if($this->f3->exists('POST.value_2')){
    			if($this->Configs->newPage()){
    				$this->f3->reroute('/');
    			}
    		}
    	}
    	// slugify $this->f3->set('PO' 
    	
    }
	
    
    function newlink()
    {
    	$menus = array(1 => 1,2 => 2,3 => 3);
    	$this->f3->set('menus',$menus);
    	if($this->Members->isAuthenticated()){
    		if($this->f3->exists('POST.value')){
    			$this->f3->set('POST.tipo','link');
    			if($this->Configs->save()){
    				$this->f3->reroute('/');
    			}
    		}
    	}
    	// slugify $this->f3->set('PO' 
    	
    }
    
    
    function delete()
    {
    	
    	if($this->Members->isAuthenticated()){
    		$this->Configs->delete($this->f3->get('PARAMS.p1'));
    		
    		$this->msg("Apagado!");
    		$this->f3->reroute('/');
    	}
    }
}
