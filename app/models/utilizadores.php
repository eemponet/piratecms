<?php

namespace App\Models;
class Utilizadores extends Model {
	
	public $tableName = 'utilizadores';
	
	
	public function __construct() {
		parent::__construct();
		
		
	}
	
	public static function getCombo()
	{
		// return parent::getRows();
	}
	
}




