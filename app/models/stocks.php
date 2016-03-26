<?php

namespace App\Models;
class Stocks extends Model {
	/*
	create table stock(
  `id` int NOT NULL AUTO_INCREMENT,
   data datetime,
   armazem int,
   danificados int,
   expirados int,
   recusados_pendentes int,
   recuperados int,
     created TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  modified DATETIME,
   primary key(id)
)
	*/
	public $fields = array(
		array('name' => 'id', 'type' => 'hidden', 'filter' => true),
		array('name' => 'data', 'type' => 'datepicker'),
		array('name' => 'armazem', 'type' => 'text'),
		array('name' => 'danificados', 'type' => 'text'),
		array('name' => 'expirados', 'type' => 'text'),
		array('name' => 'recusados_pendentes', 'type' => 'text'),
		array('name' => 'recuperados', 'type' => 'text'),
		
		);
	
	public $tableName = 'stocks';
	public $force_translation = false;
	// public $filters = array('nome','morada','telefone','localidade','codigo','visivel','referencia');
	// public $primaryKey = 'cd_loja';
	
	public function __construct() {
		parent::__construct();
		
		// $data = Utilizadores::getCombo();
		// $data = \App\Plugins\db::all("utilizadores");
		$data = $this->db->getCombined("utilizadores","id,nome","id","nome","order by nome");
		$this->fields[] = array('name' => 'utilizador_id','type' => 'combobox','data' => $data);
	}
	
	
}




