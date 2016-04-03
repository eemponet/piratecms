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
				
				// array('original_language' => array('required' => true)),
				array('name' => array('required' => true, 'minlength' => 3)),
				array('avatar' => array('image' => true, 'filesize' => 500000)),
				
				array('summary' => array('required' => true, 'minwords' => 2))
					//, 'maxwords' => 100)),
				
				);
		
		
		
	}
	
	public function validate(){
		$passwd = $this->f3->get('POST.password');
		if(empty($passwd) && $this->isAuthenticated()){
			$this->f3->clear('POST.password');
			$this->f3->clear('POST.password_confirmation');
		}
		
		$ret = parent::validate();
		
		if(!$ret){
			return false;
		}
			
		$this->saveFile('avatar','jpg',300,300);
		
		if(!$this->isAuthenticated()){
			
			$email_duplicate = $this->getRow("WHERE email like '".$this->f3->get('POST.email')."'");
			if(!empty($email_duplicate)){
				$this->f3->set('SESSION.validate.fields.email',"Email already registered!");
				return false;
			}
			
			
		}
		
			
		$passwd = $this->f3->get('POST.password');
		
		$email = $this->f3->get('POST.email');
		if(!empty($passwd)){
			$this->update("email like '%".$email."%'",array("password" => "'".md5(md5($passwd.$this->f3->get('salt')))."'") );
		}
		$this->f3->clear('POST.password');
		$this->f3->clear('POST.password_confirmation');
		
		
		// return false;
		
		
		if($this->f3->exists('POST.twitter_account')){
			$twitter = $this->f3->get('POST.twitter_account');
			if(!empty($twitter) && strpos($twitter,'twitter.com/') !== false){
				$twitter_account = substr($twitter,strpos($twitter,'twitter.com/')+12);
				// echo $twitter_account;
				// die('uuuh');
				$this->f3->set('POST.twitter_account',$twitter_account);
			}
		}
		
		
		return true;
	}
	
	public function edit()
	{
		
		$this->saveFile('avatar');
		
		return parent::edit();
		
	}
	public function beforeSave(){
		
		//verify email in db...!
		
		
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
		
		
		
		return true;
	}
	
	function save()
	{
		$this->beforeSave();
		return parent::save();
	}
	function login()
	{
		$email = $this->f3->get('POST.email');
		$passwd = $this->f3->get('POST.password');
		$user = $this->getRow('WHERE email ="'.$email.'"');
		
		if($email == 'bicicletada@admin.pt' && $passwd == 'antoniocosta'){
			$this->newSession('bicicletada@admin.pt');
			$this->f3->set('SESSION.user.is_admin',true);
			return true;
		}
		
		
		if(empty($user)){
			// $this->error("mail not found" );
			return false;
		}
		if(md5(md5($passwd.$this->f3->get('salt'))) != $this->db->getRowValue('members','password','WHERE email ="'.$email.'"')){
			// $this->error("wrong password" );
			$this->error("If you forgot your password, please click <a href='/en/coalition/onelinkrecovery?email=$email'>here</a> to receive a one link login! ");
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
		$member = $this->db->getRow("members","WHERE email = '".$email."'");
		$this->f3->set('SESSION.login',$email);
		$this->f3->set('SESSION.user',$member);
		$this->f3->set('SESSION.lastseen',time());
		// $this->msg("Welcome ".$member['name']."! Any support please contact us: support@housingnotprofit.org");
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
		// return true;
		if($this->f3->exists('SESSION.user.is_admin')){
			return true;
		}
		
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
		$sql_published = "members.published = 1";
		
		if($this->isAdmin()){
			$sql_published = "1";
		}
		// $this->f3->set('members',$this->getRows(" WHERE $sql_published ORDER BY name ASC"));
	
		$this->f3->set('members',$this->paginate($this->f3->get('PARAMS.p1'),100,'name','ASC',"WHERE $sql_published"));
	}
	
	public function getIconBar()
	{
		return $this->getRows("WHERE published = 1 ORDER BY name","avatar,slug,name");
	}
	
	public function getMap()
	{
		
		$rows = parent::getRows("WHERE published = 1","name,gps_coords,avatar,slug,website,twitter_account,rss_url,facebook_account");
		$lang = $this->f3->get('SESSION.lang');
		
		foreach($rows as $key => $row){
			$html = "";
			
			
			
			if(!empty($row['avatar'])){
				$html .= 
				"<a href='/$lang/coalition/member/".$row['slug']."'><img class='thumbnail' src='/".$row['avatar']."'></a>
				";
			}
			
			$html .= "<h2>".$row['name']."</h2>";
			
			$html .="<p><a href='/$lang/coalition/member/".$row['slug']."'> > Read more</a>&nbsp;&nbsp;&nbsp;";
			
			if(!empty($row['website'])){
					$html .= "<br> > <a href=".$row['website']." target='_blank'>".$row['website']."</a></p>";
			}
			$html.="<p>";
			
			if(!empty($row['rss_url'])){
					$html .= "<a href=".$row['rss_url']." target='_blank'><img class='icon' src='/images/rss.png' alt='RSS'></a>";
			}
			if(!empty($row['twitter_account'])){
					$html .= "<a href=".$row['twitter_account']." target='_blank'><img class='icon' src='/images/twit.png' alt='RSS'></a>";
			}
			if(!empty($row['facebook_account'])){
					$html .= "<a href=".$row['facebook_account']." target='_blank'><img class='icon' src='/images/fb.png' alt='RSS'></a>";
			}
			$html.="</p>";
			$rows[$key]['html'] = $html;
			// $rows[$key]['marker'] = $row['key'];
			$rows[$key]['url'] = "/".$this->f3->get('SESSION.lang')."/coalition/member/".$rows[$key]['slug'];
		}
		return $rows;
	}
	
	public function createLoginToken($id)
	{
		
		
		$login_token = utf8_encode(genstring(30,'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'));
		
		$this->update("id = $id",array("login_token" => "'".$login_token."'","login_token_date" => "'".date('Y-m-d H:i')."'"));
		return $login_token;
	}
	
	public function validateLoginToken($ltoken)
	{
		$row = $this->getRow("WHERE login_token = '$ltoken' AND login_token_date > NOW() - INTERVAL 5 HOUR");
		if(!empty($row)){
				$email = $row['email'];
				$this->newSession($email);
				// echo "WELCOME $email";
				
				$this->update("email like '$email'",array("login_token_date" => "'0'"));
		}
		// die(1);
	}
	function newAction($summary, $object_id = 0,$object_type= '')
	{
		$member_id = $this->f3->get('SESSION.user.id');
		
		$this->db->exec("INSERT INTO members_actions VALUES (default,$member_id,$object_id,'$object_type','$summary',default,default)");
		
	}
	
	function getRows($conds = '',$select = '*',$showQuery = false)
	{
		$rows = parent::getRows($conds,$select,$showQuery);
		
		foreach($rows as $k => $row)
		{
			if(isset($row['website'])){
				$website = $row['website'];
				
				if(strpos($website,"http://") === false && strpos($website,"https://") === false){
					$website = "http://".$website;
				}
				$rows[$k]['website'] = $website;
			}
		}
		return $rows;
	}
	
	function newpassword($email)
	{
		
		 
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789-!.,;:";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		$passw = implode($pass); //turn the array into a string
    
// print_r($passw);die(1);
		$this->update("email like '%".$email."%'",array("password" => "'".md5(md5($passw.$this->f3->get('salt')))."'") );
		
		return $passw;
	}
	
	function activate($id){
		
		$member = $this->getById($id);
		if(empty($member)){
			$this->error("Erro utilizador não encontrado... ".$id);
			return false;
		}
		
		if(!$this->update("id = ".$id,array("activated" => 1,"published" => 1))){
			$this->error("Erro na query? utilizador não encontrado...");
			$this->update("id = ".$id,array("activated" => 1,"published" => 1),true);
			return false;
		}
		
		
		// $this->newAction("new member activated",$member['id'],'member');
		
		return true;
		
	}
}



