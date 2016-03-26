<?php


namespace App\Models;
class Pedidosestados extends Model {
	
	public $tableName = 'pedidos_estados';
	public $force_translation = false;
	//public $filters = array('nome','morada','telefone','localidade','codigo','visivel','referencia');
	public $primaryKey = 'id';
	//public $hasOne = array(array("Model" =>"Pedidosestados","referenceKey" => "tipo_estado_id"),
     //                   );
	
	
}


