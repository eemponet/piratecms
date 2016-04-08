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
	
	public function getPage($slug){
		$row = $this->getRow("WHERE value like '$slug' AND tipo = 'htmlpage'");
		$row['link'] = $menu['value'];
		$row['title'] = $menu['value_2'];
		
		return $row;
		// $row['value'] = 'dsa';
	}
	
	
	public function getLink($route){
		$row = $this->getRow("WHERE value like '$route' AND tipo = 'link'");
		$row['link'] = '/page/show/'.$menu['value'];
		$row['title'] = $menu['value_2'];
		
		return $row;
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
	
	public function getRows($conds = '',$select = '*',$showQuery = false){
		$rows = parent::getRows($conds,$select,$showQuery);
		
		foreach($rows as $key => $row){
			$tipo = $row['tipo'];
			
			if($tipo == 'link'){
				$rows[$key]['link'] = $row['value'];
				$rows[$key]['title'] = $row['value_2'];
			}
			
			if($tipo == 'htmlpage'){
				$rows[$key]['link'] = '/page/show/'.$row['value'];
				$rows[$key]['title'] = $row['value_2'];
			}
			
			
		}
		
		return $rows;
	}
	
}

