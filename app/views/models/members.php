<?php


namespace App\Models;
class Members extends Model {
	
	public $tableName = 'members';
	public $translated_fields = array();
	public $force_translation = false;
	public $destination_dir = 'images/members/';
	public $filters = array("published","name","website","summary");
	
	public function __construct() {
		
		parent::__construct();
		
		$this->validations = array(
				
				array('email' => array('required' => true,'email' => true)),
				
				array('password' => array('minlength' => 7,'confirm' => 'password_confirmation')),
				
				array('original_language' => array('required' => true)),
				array('name' => array('required' => true, 'minlength' => 3)),
				
				array('website' => array('required' => true, 'minlength' => 5)),
				array('avatar' => array('image' => true, 'filesize' => 500000)),
				
				array('summary' => array('required' => true, 'minwords' => 20, 'maxwords' => 100)),
				
				);
		
		
		
	}
	
	public function validate(){
		$this->saveFile('avatar');
		
		if(!$this->isAuthenticated()){
			
			$email_duplicate = $this->getRow("WHERE email like '".$this->f3->get('POST.email')."'");
			if(!empty($email_duplicate)){
				$this->f3->set('SESSION.validate.fields.email',"Email already registered!");
				return false;
			}
			
			
		}else{
			
			$passwd = $this->f3->get('POST.password');
			
			if(empty($passwd)){
				
				$this->f3->clear('POST.password');
				$this->f3->clear('POST.password_confirmation');
			}
			
			// return false;
		}
		
		
		
		return parent::validate();
	}
	
	public function edit()
	{
		
		$this->saveFile('avatar');
		
		return parent::edit();
		
	}
	public function beforeSave(){
		
		//verify email in db...!
		
		if(!$this->isAuthenticated()){
			
			$slug = $this->f3->get('POST.slug');
			
			if(empty($slug)){
				$slug = $this->slugify($this->f3->get('POST.name'));
				$this->f3->set('POST.slug',$slug);
				
				$slug_duplicate = $this->getRow("WHERE slug like '".$slug."'");
				if(!empty($slug_duplicate)){
					$slug .= uniqid();
					$this->f3->set('POST.slug',$slug);
				}
			}
			
			
			// $this->f3->set('POST.approved',true);
			$passwd = $this->f3->get('POST.password');
			$this->f3->set('POST.password',md5(md5($passwd.$this->f3->get('salt'))));
			
			$this->saveFile('avatar');
			
			$this->f3->set('POST.created',date("Y-m-d H:i"));
		}
		
		
		
		
		return true;
	}
	
	function login()
	{
		$email = $this->f3->get('POST.email');
		$passwd = $this->f3->get('POST.password');
		$user = $this->getRow('WHERE email ="'.$email.'"');
		if(empty($user)){
			// $this->error("mail not found" );
			return false;
		}
		if(md5(md5($passwd.$this->f3->get('salt'))) != $this->db->getRowValue('members','password','WHERE email ="'.$email.'"')){
			$this->error("wrong password" );
			return false;
		}
		
		$member = $this->getRow('WHERE email ="'.$email.'"');
		
		if( $member['published'] == 0 && $member['is_admin'] != 1){
			$this->error("Error login, your account wasn't approved yet!" );
			return false;
		}
		
		$this->newSession($email);
		
		return true;
	}
	
	function bypassLogin($email){
		return false;
		$this->newSession($email);
	}
	
	function newSession($email){
		
		$this->f3->set('SESSION.login',$email);
		$this->f3->set('SESSION.user',$this->db->getRow("members","WHERE email = '".$email."'"));
		$this->f3->set('SESSION.lastseen',time());
		
	}
	
	function getUser($id){
		$member = $this->getById($id);
		
		unset($member['password']);
		
		return $member;
	}
	
	function isAuthenticated(){
		if(!$this->f3->exists('SESSION.user')){
			return false;
		}
		return true;
	}
	
	function isAdmin(){
		if(!$this->db->getRow("members","WHERE id = '".$this->f3->get('SESSION.user.id')."' AND is_admin")){
			return false;
		}
		return true;
	}
	
	function getAll($approved = true){
		// return $this->getRows("WHERE approved = false");
		
		$conds = "WHERE 1";
		
		foreach($this->filters as $filter){
			if($this->f3->exists("GET.$filter")){
				$conds .= " AND LOWER($filter) like '%".strtolower($this->f3->get("GET.$filter"))."%'";
			}
		}
		
		return parent::paginate($page, 10 , "created", "DESC",$conds);
	}
	
	function isPublished(){
		if(!$this->db->getRow("members","WHERE id = '".$this->f3->get('SESSION.user.id')."' AND published = 1")){
			return false;
		}
		return true;
	}
	
	function getPages()
	{
		$sql_published = "members.published = 1 AND members.activated = 1";
		
		if($this->isAdmin()){
			$sql_published = "1";
		}
		
		$this->f3->set('members',$this->paginate($this->f3->get('PARAMS.p1'),10,'name','ASC',"WHERE $sql_published"));
	}
	
	
	public function getMap()
	{
		return $this->getRows("WHERE published = 1","name,gps_coords,avatar,slug");
	}
}


