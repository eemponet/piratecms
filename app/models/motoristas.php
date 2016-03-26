<?php

namespace App\Models;
class Motoristas extends Model {
	
	public $tableName = 'colaboradores';
	
	
	public function __construct() {
		parent::__construct();
		
		
	}
	
	public static function getCombo()
	{
		// return parent::getRows();
	}
	
}




