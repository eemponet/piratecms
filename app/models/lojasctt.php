<?php


namespace App\Models;
class Lojasctt extends Model {
	
	public $tableName = 'lojas_ctt';
	public $force_translation = false;
	public $filters = array('nome','morada','telefone','localidade','codigo','visivel','referencia');
	public $primaryKey = 'cd_loja';
	
	public $fields = array(
		array('name' => 'cd_loja', 'type' => 'hidden', 'filter' => true),
		array('name' => 'referencia', 'type' => 'text'),
		array('name' => 'nome', 'type' => 'text'),
		array('name' => 'morada', 'type' => 'text'),
		array('name' => 'codigo', 'type' => 'text'),
		array('name' => 'localidade', 'type' => 'text'),
		array('name' => 'telefone', 'type' => 'text'),
		array('name' => 'horario1', 'type' => 'text'),
		array('name' => 'horario2', 'type' => 'text'),
		array('name' => 'horario3', 'type' => 'text'),
		array('name' => 'horario4', 'type' => 'text'),
		
		);
	
	public function __construct() {
		parent::__construct();
		
	}
	
	
	
}


