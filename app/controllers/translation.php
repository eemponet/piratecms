<?php

namespace App\Controllers;


	
class Translation{
	
	static $lang = array(
		"en" => array(
			"validation_required" => "This field is required",
			"validation_minlength" => "This field, has a minimum length of characters",
			"validation_minwords" => "This field, has a minimum length words",
			"validation_maxwords" => "This field, has a maximum length words",
			"validation_imagetype" => "Wrong image mimetype, please upload only jpg,jpeg or png",
			"validation_filesize" => "File too big, please upload a smaller file.",
			"validation_mismatch" => "Please ensure you wrote the same password in the confirmation field. ",
			"validation_regex" => "Invalid field",
			"validation_data" => "Invalid field [Ex. '1980-10-05']",
			"validation_email" => "Invalid email",
			"validation_url" => "Invalid url",
			"validation_error" => "Please, review the form and fill out the fields with correct values!",
			"validation_twitter" => "Please ensure you write a valid twitter account @Account",
			
			"submission_ok" => "Thank you for your submission!",
			"submission_error" => "Error, could not submit, please contact the sys admin!",
			
			"form_ok" => "Your data was saved successfuly!",
			"form_error" => "Error, saving your data, please contact the sys admin!",
			
			),
		"pt" => array("validation_required" => "Este campo &eacute; obrigat&oacute;rio!",)
		);
	
	static function getTranslation($language,$key){
		
		
		
		if(isset(self::$lang[$language][$key])){
			return self::$lang[$language][$key];
		}
		
		if(isset(self::$lang["en"][$key])){
			return self::$lang["en"][$key];
		}
		
		return "err";
	}
	
	function i18n($args)
	{
		echo "<pre>";
		print_r($args);
		echo "</pre>";
		$langdefs = $this->f3->get('langdefs');;
		if(isset($langdefs[$id])){
			return $langdefs[$id];
		}
		return "ERR";
		return $id;
	}

}



