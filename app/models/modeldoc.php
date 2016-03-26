<?php


namespace App\Models;
class ModelDoc extends Model {
	
	public $tableName = 'articles';
	public $translated_fields = array();
	public $force_translation = false;
	public $dst_dir = 'images/articles/';
	
	public function __construct() {
		
		parent::__construct();
		
		$this->validations = array(
				
				array('title' => array('required' => true, 'minlength' => 20)),
				array('ffield_name' => array('email' => true, 'minlength' => 20)),
				array('title' => array('regex' => "data")),
				array('title' => array('regex' => "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/")),
				array('title' => array('email' => true)),
				
				);
		
		
		
	}
	
	
	public function beforeSave(){
		
		if(!$this->validate())
		{
			return false;
		}
		// $imagem = $this->f3->get('FILES.nova_imagem');
		
		// if(!empty($imagem['name'])){
			
		// 	$file = $this->saveFile('nova_imagem',$this->dst_dir );
		// 	if(!$file){
		// 		$this->f3->set('error_msg','Erro a subir imagem...');
		// 		return false;
		// 	}
		// 	$this->f3->set('POST.imagem',$this->dst_dir.$file);
		// }
		// else{
		// 	$this->f3->clear('POST.imagem');
		// }
		
		// return true;
		
		return true;
	}
}


