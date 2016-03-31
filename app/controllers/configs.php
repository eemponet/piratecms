<?php

namespace App\Controllers;

class Configs extends Controllerapp{
	
	//insert into configs(default,'htmlpage','id_page','html...',default,default)
	// insert into configs values(default,'htmlpage','intro_memberslist','html...',default,default,default,default);
	 
	// public $translate = true;
	// protected $models = array("Pages","Areas","Noticias","Definicoes","Traducoes");
	protected $logs_dir = '';
	public $title = 'Configura&ccedil;es gerais';
	protected $models = array('Configs');
	
	function __construct()
	{
		parent::__construct();
		
		// $this->f3->set('SESSION.lastseen',time());
		// $this->f3->set('SESSION.ip',$_SERVER['REMOTE_ADDR']);
		
	}
	
	function index()
	{
		$this->f3->set('pages',$this->Configs->getRows("WHERE tipo = 'htmlpage'"));
		
	}
	
	
	function mailtemplates(){
		$mails = $this->Configs->getRows("WHERE tipo like '%mail%'");
		$this->f3->set('mails',$mails);
		
		$this->f3->set('menu_topo',array(
			array('link' => '/configs','caption' => "Configurações gerais",'icon' => 'th'),
			array('link' => '/configs/createtemplate','caption' => "Novo template para mail",'icon' => 'plus')
			
			));
	}
	function edithtmlpage()
	{
		$this->f3->set('TITLE','Editar pgina');
		$this->f3->set('available_langs',$this->languagesCombo());
		
		$menus = array(1 => 1,2 => 2,3 => 3);
		$this->f3->set('menus',$menus);
		$config = $this->Configs->getRow("WHERE id = '".$this->f3->get('PARAMS.p1')."'");
		
		
		if($this->f3->exists('POST.id')){
			if($this->Configs->edit()){
				$this->f3->set('msg','Guardado!');
				$this->f3->set('SESSION.lastroute',$this->f3->get('SESSION.lroute'));
				$lastroute =  $this->f3->get('SESSION.lastroute');
				
				$this->f3->reroute($lastroute);
			}
			
		}else{
			
			$lastroute = $this->f3->get('SESSION.lastroute');
			if(strpos($lastroute,'configs') === false){
				$this->f3->set('SESSION.lroute',$lastroute);
			}
			
			$this->copyToPost($config);
		}
		
	}
	function edit()
	{
		
		
		if(!$this->f3->exists('PARAMS.p1')){
			$this->f3->reroute('/configs');
		}
		
		$config = $this->Configs->getRow("WHERE id = '".$this->f3->get('PARAMS.p1')."'");
		
		if(empty($config)){
			$this->reroute('configs');
		}

		if($this->f3->exists('POST.id')){
			if($this->Configs->edit()){
				$this->f3->set('msg','Guardado!');
			}
			// $this->f3->reroute('/bo/'.$this->controller);
		}else{
			$this->copyToPost($config);
		}
	}
	
	
	
}
