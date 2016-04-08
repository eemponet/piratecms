<?php
namespace App\Controllers;

class Controller{
	
	protected $f3;
	
	protected $db;
	
	public $layout = "default";
	public $title = "";
	
	public $controller = "coalition";
	public $action = "index";
	
	public $log;
	
	function __construct() {
		
		
		$this->f3 = \Base::instance();
		$this->db = new \App\Plugins\db();
		
		
		$this->log = \Base::instance()->get('log');
		
		if(!empty($this->models)){
			foreach($this->models as $model){
				$name = $model;
				$namespace = "\\App\\Models\\$name";
				$this->$model = new $namespace;
				
			}
		}
		$this->layout = $this->f3->get('LAYOUT');
	}
	
	function index()
	{
		
	}
	
	function hasPermission(){
		
		if($this->f3->exists('SESSION.login'))
		{
			$controller = $this->controller;
			foreach($this->f3->acl["auth"] as $acl_key => $acl_vals)
			{
				if(!is_array($acl_vals) && $controller == $acl_vals)
				{
					return true;
				}
				if(is_array($acl_vals) && $controller ==  $acl_key )
				{
					if(in_array($action,$acl_vals))
					{
						return true;
					}
				}
			}
			
		}
		if($this->f3->exists('SESSION.bo'))
		{
			return true;
		}
		
		if(empty($controller))
		{
			return true;
		}
		
		foreach($this->f3->acl["guest"] as $acl_key => $acl_vals)
		{
			
			if(!is_array($acl_vals) && $controller == $acl_vals)
			{
				return true;
			}
			if(is_array($acl_vals) && $controller ==  $acl_key )
			{
				if(in_array($action,$acl_vals))
				{
					return true;
				}
			}
		}
		
		/**
		if(array_search($controller,$this->f3->acl["guest"]) !== false){
		if(is_array($this->f3->acl["guest"][array_search($controller,$this->f3->acl["guest"])] ))
		{
		//check actions
		}else{
		$hasPermissions = true;
		}
		}**/
		
		return false;
	}
	
	function beforeroute() {
		
		$this->f3->clear('SESSION.validate');
		if($this->f3->exists('POST.id'))
		{
			$this->f3->set('POST.modified',date("Y-m-d H:i:s"));
		}
		if($this->f3->exists('SESSION.login'))
		{
			$this->f3->set('POST.login',$this->f3->get('SESSION.login'));
		}
		
		$action = $this->f3->get('PARAMS.action');
		$controller = $this->f3->get('PARAMS.controller');
		
		if(empty($action)) $action = $this->action;
		if(empty($controller)) $controller = $this->controller;
		
		if($this->f3->exists('LANG') && !isset($this->donttranslate) ){
			$lang_def = $this->f3->get('LANG');
			$lang = $lang_def;
			
			if( $this->f3->exists('PARAMS.lang')){
				$lang_params = $this->f3->get('PARAMS.lang');
				$langs = $this->f3->get('LANGS');
				if(!in_array($lang_params,$langs)){
					$this->f3->reroute('/'.$lang_def);
				}else{
					$lang = $lang_params;
				}
			}else{
				$this->f3->reroute('/'.$lang_def);
			}
			
			$this->f3->set('lang_set',$lang);
			$this->f3->set('SESSION.lang',$lang);
			
		}
		
		
		
		if(!$this->hasPermission())
		{
			// $this->f3->set('SESSION.error_msg',"Reserved Zone!");
			$this->error("Reserved area!");
			$this->f3->reroute("/");
		}
		
		
		
		$this->f3->set('controller',$controller);
		$this->f3->set('action',$action);
		$this->controller = $controller;
		$this->action = $action;
		if(!empty($this->viewsPrefix)){
			$this->f3->set('content',$this->viewsPrefix.'/'.$controller.'/'.$action.'.htm');
		}else{
			$this->f3->set('content',$controller.'/'.$action.'.htm');
		}
		
		
		
		
		$charset = 'utf-8';
		if($this->f3->exists('CHARSET')){
			$charset = $this->f3->get('CHARSET');
		}
		$this->f3->set('CHARSET',$charset);
		
		
		// if(strlen($this->title) < 1)
		// {
		// 	$this->title =  $this->f3->get('PARAMS.controller').' '. $this->f3->get('PARAMS.action');
		// }
		// $this->f3->set('title',$this->title);
		
		$this->setlocale();
        
	}
	
	
	function setlocale()
	{
		$lang = $this->f3->get('SESSION.lang');
		if(file_exists("app/langs/$lang.php")){
			include("app/langs/$lang.php");
		}else{
			include("app/langs/en.php");
		}
		global $langdefs;
		$this->f3->set('langdefs',$langdefs);
		
		
		$langs = array("en" => "en_EN","fr" => "fr_FR","pt" => "pt_PT","es" => "es_ES","gr" => "Greek",
			"it" => "it_IT","hr" => "hr_HR");
		setlocale(LC_TIME,$langs[$lang]);
	}
	function afterroute() {
		$title = '';
		if(!empty($this->title)){
			$title .= $this->title.' - ';
		}
		if($this->f3->exists('SITE_TITLE')){
			$title .= $this->f3->get('SITE_TITLE');
		}
		
		$this->f3->set('TITLE',$title);
		
		if($this->f3->exists('SESSION.msg'))
		{
			$this->f3->set('msg',$this->f3->get('SESSION.msg'));
			$this->f3->clear('SESSION.msg');
		}
		
		if($this->f3->exists('SESSION.error_msg'))
		{
			$this->f3->set('error_msg',$this->f3->get('SESSION.error_msg'));
			$this->f3->clear('SESSION.error_msg');
		}
		if(!empty($this->menu_right))
		{
			$this->f3->set('menu_right',$this->menu_right);
		}
		
		
		
		
		echo \Template::instance()->render('layouts/'.$this->layout.".htm");
		
		if(!in_array($this->layout,array('empty','blank'))){
			// $this->f3->set('SESSION.lastroute',str_replace($this->f3->get('BASE'),"",$this->f3->get('URI')));
			$this->f3->set('SESSION.lastroute',$this->curPageURL());
		}
		
		// echo "<pre>";
		// print_r($_SESSION);
		// echo "</pre>";
		
	}
	
	function goback()
	{
		$referer = $this->f3->get('SERVER.HTTP_REFERER');
		if(!empty($referer)){
			$this->f3->reroute($referer);
		}else{
			$this->f3->reroute($this->f3->get('SESSION.lastroute'));
		}
	}
	
	function copyToPost($array){
		foreach($array as $key => $val){
			$this->f3->set("POST.$key",$val);
		}
	}
	
	function reroute($controller,$action = 'index')
	{
		
		
		if($this->f3->exists('LANG')){
			$lang = '/'.$this->f3->get('lang_set');
		}
		// print_r($_SESSION);
		// die('SEM MAIL?');
		$this->f3->reroute($lang.'/'.$controller.'/'.$action);
	}
	
	function setTitle($title)
	{
		$this->title = $title;
		$this->f3->set('title',$title);
	}
	
	public function getTranslation($name)
	{
		return \App\Controllers\Translation::getTranslation($this->f3->get('lang_set'),$name);
	}
	
	public function log($msg)
	{
		$msg = date('Y-m-d h:i').":: $msg";
		$this->log->write($msg);
	}
	
	public function notifyAdmins()
	{
		$admins = $this->db->getRows("members","WHERE is_admin = 1","name,email");
		
		// print_r($admins);
	}
	
	
	
	function curPageURL() {
		$url = $this->f3->get('BASE');
		
		$pageURL = 'http';
		if (!empty($_SERVER["HTTPS"])) {
			$pageURL .= "s";
		}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		// echo $pageURL;
		return $pageURL;
		
		// $pageURL = 'http';
		// if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		// $pageURL .= "://";
		// if ($_SERVER["SERVER_PORT"] != "80") {
		// 	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		// } else {
		// 	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		// }
		// return $pageURL;
	}
	
	function lang(){
		return $this->f3->get('lang_set');
	}
}
