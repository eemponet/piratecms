<?php


namespace App\Models;
class Pedidos extends Model {
	
	public $tableName = 'pedidos';
	public $force_translation = false;
    public $filters = array('id','morada_recolha_localidade','morada_entrega_destinatario','tipo_servico','urgencia','cliente_id' => array("type" => "number"),'colaborador_id' => array("type" => "number"));
	//public $filters = array('nome','morada','telefone','localidade','codigo','visivel','referencia');
	public $primaryKey = 'id';
	
    public $hasMany = array(array("Model" =>"Pedidostiposestados","referenceKey" => "tipo_estado_id"),
                            array("Model" => "Utilizadores", "referenceKey" => "cliente_id", "foreignKey" => "id"),
                            array("Model" => "Motoristas", "referenceKey" => "colaborador_id", "foreignKey" => "colaboradores_id")                                
                        );

	
	
}


