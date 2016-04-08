<?php
namespace App\Controllers;

class ControllerApp extends Controller{
	
	private $acl = 
	array(
	"guest" => array(
		"articles" => array('index' => true,'view' => true,'articles' => true,"publish" => true),
		
		"coalition" => array("index" => true,"join" => true,"login" => true,"member" => true,"thankyou" => true,"map" => true,"memberslist" => true,"recover" => true,"members" => true,"onelinkrecovery" => true),
		
		"actions" => array("index" => true,"rss" => true,"social" => true,"view" => true,"map" => true,"publish" => true,"thankyou" => true,"upcoming" => true,"past" => true,"etapa1" => true,"etapa2" => true),
		"campaigns" => array("index" => true),
		"resources" => true,
		
		"live" => array("index" => true,"rss" => true,"social" => true),
		"page" => array("lang" => true,"show" => true),
		"scripts" => array("rss" => true, "twitter" => true)),
	
						 
	"auth" => array("coalition" => array("logout" => true,"edit" => true,"linkedit" => true),"actions" => array("delete" => true)), //AUTH USER
	
	"admin" => array("coalition" => array("moderate" => true))  //AUTH ADMIN
						 );
	protected $models = array("Members","Configs");
		
	function __construct()
	{
		$newlogin = false;
		if(isset($_GET['ltoken']))
		{
			$login_token = $_GET['ltoken'];
			
			// $login_token = $members->createLoginToken(16);
			// echo $login_token;
			$members = new \App\Models\Members();
			$members->validateLoginToken($login_token);
			$newlogin = true;
			// die($login_token);
		}
		parent::__construct();
		// $this->msg("Welcome!");
		// if($newlogin){
		// 	$this->f3->set('SESSION.msg','Bem-vindo!');
		// }
		$langs = array(
			array("alias" => "pt", "title" => "Portugues"),
			array("alias" => "es", "title" => "Espanol"),
			array("alias" => "fr", "title" => "French"),
				array("alias" => "en", "title" => "English"),
				
				
				/*array("alias" => "gr", "title" => "Greek"),
				array("alias" => "it", "title" => "Italiano"),
				array("alias" => "hr", "title" => "Hungarian"),*/
				);
		
		
		$this->f3->set('langs',$langs);
		
		
		// $meta = array("description" => "Page description. No longer than 155 characters.");
		// https://moz.com/blog/meta-data-templates-123
		// $this->f3->set('meta',$meta);
		
		// $this->f3->clear('SESSION.msg');
		// $this->f3->clear('SESSION.error_msg');
	}
	
	function beforeroute(){
		$this->Configs = new \App\Models\Configs();
		
		
		
		
		parent::beforeroute();
		
		$page_id = '/'.$this->controller.'/'.$this->action;
		
		$page = $this->Configs->getPage($page_id);
		
		
		if(!isset($page['id'])){
			$page = $this->Configs->getLink($page_id);
		}
		
		if(!empty($page)){
			$this->title = trim($page['value_2']);
			$this->description = strip_tags($page['value_1']);
			$this->f3->set('page',$page);
		}
		
		
		for($i=1;$i<=4;$i++){
			$menu = $this->Configs->getRows("WHERE value_3=$i AND (tipo='htmlpage' OR tipo = 'link') ORDER BY orderby");
			// ,"value_2 as title,CONCAT('/page/show/',value) as link
			// $menu['title'] = $menu['value_2'];
			// $menu['link'] = 'page/show/'.$menu['value'];
			// foreach($menu as $k => $item){
				// if()
				// $menu[$k]['link'] = '/page/show/'.$item['value'];
				// $menu[$k]['title'] = $item['value_2'];
			// }
			$menus[] = $menu;
		}
		
		
		$this->f3->set('menu_principal',$menus);
		
		
		
	}
	
	function afterroute(){
		$titulo = "";
		if(!$this->f3->exists('title') && !empty($this->title) ){
			$this->f3->set('title',$this->title);
		}
		
		
		
		// if(!empty($this->title)){
				// $title .= ' - ';
				// $title .= $this->title;
				// $titulo .= "DSADSA".$this->title;
				// $titulo = $this->title;
			// }
		if($this->f3->exists('SITE_TITLE')){
			$title = $this->f3->get('SITE_TITLE');
			if(!empty($this->title)){
				// $title .= ' - ';
				// $title .= $this->title;
				$titulo .= $this->title;
				// $titulo = $this->title;
			}
		}
		if(!$this->f3->exists('meta.title')){
		$this->f3->set('meta.title',$titulo." - ".$title);
		}
		// $this->f3->set('meta.title',$title);
		
		
		$description = $this->f3->get('description');
		if(!empty($this->description)){
			$description = $this->description;
		}
		$description = substr($description,0,155);
		if(!$this->f3->exists('meta.description')){
			$this->f3->set('meta.description',$description);
		}
		$url = $this->f3->get('url').$this->f3->get('uri');
		
		$image = $url.'images/cache/cartaz.jpg';
		if(isset($this->image)){
			$image = $this->image;
		}
		if(!$this->f3->exists('meta.image')){
		$this->f3->set('meta.image',$image);
		}
		
		$this->f3->set('meta.site_name',$this->f3->get('SITE_TITLE'));
		
		$this->f3->set('meta.url',$this->curPageURL());
		parent::afterroute();
		
		
		
		
	}
	function hasPermission(){
		
		$action = $this->f3->get('PARAMS.action');
		$controller = $this->f3->get('PARAMS.controller');
		if(empty($action))
		{
			$action = "index";
		}
		
		
		// echo $controller;
		
		$Members = new \App\Models\Members();
		$this->f3->set('members_icon_bar',$Members->getIconBar());
		
		if(empty($controller))
		{
			return true;
		}
		
		if($Members->isAdmin()){
			return true;
		}
		
		if($Members->isAuthenticated()){
			
			if(isset($this->acl["auth"][$controller]))
			{
				if (!is_array($this->acl["auth"][$controller])){
					return true;
				}
				if(isset($this->acl["auth"][$controller][$action]) && $this->acl["auth"][$controller][$action]){
					return true;
				}
			}
		}
		
		
		
		if(isset($this->acl["guest"][$controller])){
			if (!is_array($this->acl["guest"][$controller])){
				return true;
			}
			if(isset($this->acl["guest"][$controller][$action]) && $this->acl["guest"][$controller][$action]){
				return true;
			}
		}
		// return true;
		return false;
	}
	
	function msg($msg){
		$msgs = $this->f3->get('SESSION.msg');
		$msgs[] = $msg;
		
		$this->f3->set('SESSION.msg',$msgs);
		
		$this->log($msg);
	}
	function error($msg){
		$msgs = $this->f3->get('SESSION.error_msg');
		$msgs[] = $msg;
		$this->f3->set('SESSION.error_msg',$msgs);
		
		$this->log("error: ".$msg);
	}
	
	function genstring($tamanho = 10, $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#!)(=&'){
		
		return substr(str_shuffle($alfabeto), 0, $tamanho);
	}
	
	function languagesCombo(){
		$available_langs = array();
		foreach($this->f3->get('langs') as $lang){
			$available_langs[$lang["alias"]] = $lang["title"];
		}
		
		return $available_langs;
	}
	
	function notifyMods($subject, $message, $from) 
	{
		$mods = $this->db->getRows('members','WHERE is_admin = 1 AND activated = 1','name,email,id');
		$to = "";
		
		$members = new \App\Models\Members();
		
		foreach($mods as $mod){
			$to = $mod['name']." <".$mod['email']."> ";
			
			$login_token = $members->createLoginToken($mod['id']);
			$message = str_replace("%login_token%",$login_token,$message);
			
			$this->sendMail($to,$subject,$message,$from);
		}
		
	}
	
	function oneClickMail($member_id,$to, $subject, $message, $from = '') 
	{
		if(empty($from)){
			$from =  $this->f3->get('SITE_TITLE')." <".$this->f3->get('SUPPORT_MAIL').">";
		}
		$members = new \App\Models\Members();
		$login_token = $members->createLoginToken($member_id);
		$message = str_replace("%login_token%",$login_token,$message);
		
		$body = "<html><head></head>
		<body>
		<table>
			<tr>
				<td>
					<h1><font color='#866B8C' face='Arial, Helvetica, sans-serif'>".$this->f3->get('SITE_TITLE')."</font></h1>
				</td>
			</tr>
			<tr>
				<td height='30px' bgcolor='#866B8C' id='TdHeader'>
					<strong><font face='Geneva, Arial, Helvetica, sans-serif' color='white' size='2'>&nbsp;&nbsp;&nbsp;$subject&nbsp;</font></strong>
				</td>
			</tr>
			<tr>
				<td>$message</td>
			</tr>
			<tr>
				<td>
					<a href='".$this->f3->get('url')."'><img width='220px' src='".$this->f3->get('url')."/images/logo.png' alt='".$this->f3->get('SITE_TITLE')."'>
					</a>
				</td>
			</tr>
		</table>
		
		
		</body></html>";
		
		$headers = "From: $from\n";
		$headers .= "Reply-To: $from\n";
		$headers .= "Return-Path: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; char=utf-8\n";
		
		return mail($to, $subject, $body, $headers);
	}
	function sendMail($to, $subject, $message, $from) 
		{
		if(empty($from)){
			$from =  $this->f3->get('SITE_TITLE')." <".$this->f3->get('SUPPORT_MAIL').">";
		}
		$body = "<html><head></head>
		<body>
		<table>
			<tr>
				<td>
					<h1><font color='#866B8C' face='Arial, Helvetica, sans-serif'>".$this->f3->get('SITE_TITLE')."</font></h1>
				</td>
			</tr>
			<tr>
				<td height='30px' bgcolor='#866B8C' id='TdHeader'>
					<strong><font face='Geneva, Arial, Helvetica, sans-serif' color='white' size='2'>&nbsp;&nbsp;&nbsp;$subject&nbsp;</font></strong>
				</td>
			</tr>
			<tr>
				<td>$message</td>
			</tr>
			<tr>
				<td>
					<a href='".$this->f3->get('url')."'><img width='220px' src='".$this->f3->get('url')."/images/logo.png' alt='".$this->f3->get('SITE_TITLE')."'>
					</a>
				</td>
			</tr>
		</table>
		
		
		</body></html>";
		
		$headers = "From: $from\n";
		$headers .= "Reply-To: $from\n";
		$headers .= "Return-Path: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; char=utf-8\n";
		
		return mail($to, $subject, $body, $headers);
	}
	
	
	
	function i18n($id)
	{
		// global $langdefs;
		// $langdefs = $this->f3->get('langdefs');;
		// if(isset($langdefs[$id])){
		// 	return $langdefs[$id];
		// }
		// return $id;
		
		return i18n($id);
	}

}


