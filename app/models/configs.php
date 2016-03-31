<?php


namespace App\Models;
class Configs extends Model {
	/*
	drop table configs;
create table configs(
  `id` int NOT NULL AUTO_INCREMENT,
   tipo text,
   value text,
     created TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  modified DATETIME,
   primary key(id)
)
)
	*/
	public $tableName = 'configs';
	public $force_translation = false;
	public $translated_fields = array('value_1','value_2');
	
	public $alwaysValidate = true;
	// public $filters = array('nome','morada','telefone','localidade','codigo','visivel','referencia');
	// public $primaryKey = 'cd_loja';
	
	public function __construct() {
		parent::__construct();
		
		$this->validations = array(
			array('value_2' => array('required' => true, 'minlength' => 4)),
			);
	}
	
	public function getPage($page){
		return $this->getRow("WHERE value = '$page' AND tipo = 'htmlpage'");
	}
	
	public function newPage()
	{
		
		
    	$slug = $this->slugify($this->f3->get('POST.value_2'));
		$this->f3->set('POST.value',$slug);
    	// $this->beforeSave();
    	$this->f3->set('POST.tipo','htmlpage');
    	
    	
		return parent::save();
	}
	
	
	// public function beforeSave(){
	// 	$slug = $this->slugify($this->f3->get('POST.value_2'));
		
	// 	$this->f3->set('POST.value',$slug);
	// 	parent::beforeSave();
	// }
}

