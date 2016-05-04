<?php

namespace App\Controllers;

class Coalition extends ControllerApp{
	
	public $translate = true;
	protected $models = array("Members","MembersSocial","Aggregator","Configs","Members","Actions");
	
	// protected $models = array("Configs","Members");

	public $title = "";
	function __construct()
	{
		parent::__construct();
		
		$this->f3->set('SESSION.lastseen',time());
		$this->f3->set('SESSION.ip',$_SERVER['REMOTE_ADDR']);
		
		
		$this->f3->set('section_image',"menu_coalition.png");
		$this->f3->set('section_title',"Coalition");
		
		$options = array();
		
		// $options[] = array("link" => "/coalition","title" => "members");
		
		if($this->Members->isAdmin()){
			// $options[] = array("link" => "/coalition/members_admin","title" => "members");
		}
		
		if(!$this->Members->isAuthenticated()){
			// $options[] = array("link" => "/coalition/login","title" => "login");
			// $options[] = array("link" => "/coalition/join","title" => "join");
		}
		
		if($this->Members->isAuthenticated()){
			$options[] = array("link" => "/coalition/edit","title" => "edit information");
		}
		// if($this->Members->isPublished()){
		// 	$options[] = array("link" => "/coalition/links","title" => "aggregations");
		// }
		
		$this->f3->set('options',$options);
	}
	
	function index()
	{
		// $this->f3->set('show_banner',true);
		// $lang = '';
		// if(($this->f3->exists('LANGS') || $this->f3->exists('LANG') ) && !$this->f3->exists('PARAMS.lang')){
		// 	$lang = '/'.$this->f3->get('LANG');
		// }
		// $lang = '/'.$this->f3->get('LANG');
		// $this->f3->reroute($lang.'/page/show/manifesto');
		
		
		$this->title = 'Manifesto';
		$this->f3->set('page',$this->Configs->getPage('manifesto'));
		$this->f3->set('show_banner',true);
		
		$this->f3->set('aggregations',$this->Aggregator->getRss());
		$this->f3->set('actions',$this->Actions->upcoming());
		
	}
	
	function index2()
	{
		
		// print_r($_SESSION);
		// die('yellow');
		if(($this->f3->exists('LANGS') || $this->f3->exists('LANG') ) && !$this->f3->exists('PARAMS.lang')){
			$this->f3->reroute('/'.$this->f3->get('LANG').'/');
		}
		
		// $live = new \App\Controllers\Live();
		$this->f3->set('aggregations',$this->Aggregator->getRss(1,'',3));
		$this->f3->set('aggregations_social',$this->Aggregator->getSocial(1,'',5));
		
		// $live->social();
		
		$actions = new \App\Controllers\Actions();
		$actions->upcoming();
		
		$actions = new \App\Controllers\Actions();
		$actions->past();
		// $articles = new \App\Controllers\Articles();
		// $articles->articles();
		// $actions->social();
		
		
		
		$this->Members->getPages();
		
		// $this->f3->set('configs',$this->Configs->getRow("WHERE "));
		$this->f3->set('page',$this->Configs->getPage('index'));
	}
	
	
	function memberslist()
	{
		if(($this->f3->exists('LANGS') || $this->f3->exists('LANG') ) && !$this->f3->exists('PARAMS.lang')){
			$this->f3->reroute('/'.$this->f3->get('LANG').'/');
		}
		
		// $this->setTitle('Rede');
		
		
		$this->Members->getPages();
		
		// $this->f3->set('page',$this->Configs->getPage('rede'));
	}
	
	function join()
	{
		$this->title = "Junta-te a nós!.";
		$this->f3->set('page',$this->Configs->getPage('join'));
		
		if($this->Members->isAuthenticated()){
			$this->reroute('coalition','createmember');
		}
		if($this->f3->exists('POST.email')){
			if(!$this->Members->validate()){
				$this->error($this->getTranslation('validation_error'));
				if($this->f3->exists('FILES')){
					$this->f3->set('FILES',$this->f3->get('FILES'));
				}
			}else{
				if($this->Members->save()){
					// $this->msg($this->getTranslation('submission_ok'));
					// $this->f3->set('SESSION.member_slug',$this->f3->get('POST.slug'));
					// $this->Members->bypassLogin($this->f3->get('POST.email'));
					
					$email = $this->f3->get('POST.email');
					$name = $this->f3->get('POST.name');
					
					
					$this->notifyMods("New account to be approved $name","Please <a href='".$this->f3->get('url')."/en/coalition/member/".$this->f3->get('POST.slug')."?ltoken=%login_token%'>login here</a> to the website to review the details of the new member and activate the new account!");
					
					$this->sendMail("$name <$email>",'Your account is awaiting moderation, please wait for the activation e-mail sent by our team.','Hello,<br> we received your details, please wait for the activation of your account.');
					
					$this->reroute('coalition','thankyou');
				}else{
					$this->error($this->getTranslation('error_submission'));
				}
			}
		}
		
		$this->f3->set('section_subtitle',"Active coalition members");
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function createmember()
	{
		$this->title = "Junta-te a nós!.";
		$this->f3->set('page',$this->Configs->getPage('join'));
		
		if($this->f3->exists('POST.email')){
			if(!$this->Members->validate()){
				$this->error($this->getTranslation('validation_error'));
				if($this->f3->exists('FILES')){
					$this->f3->set('FILES',$this->f3->get('FILES'));
				}
			}else{
				if($this->Members->save()){
					// $this->msg($this->getTranslation('submission_ok'));
					// $this->f3->set('SESSION.member_slug',$this->f3->get('POST.slug'));
					// $this->Members->bypassLogin($this->f3->get('POST.email'));
					
					$email = $this->f3->get('POST.email');
					$name = $this->f3->get('POST.name');
					$member = $this->Members->getRow("WHERE email like '$email'");
					
					if(!$this->Members->activate($member['id'])){
						$this->error("Erro a activar novo membro!! ".$email." ".$member['id']);
					}else{
						
						// $this->sendMail($member['email'],'Your account has been approved','Hello,<br>You can view your <a href="'.$this->f3->get('url').'/en/coalition/member/'.$member['slug'].'">member page</a><br> Your account has been approved, you can now login to our website!');
						// $this->Members->newAction("new member activated",$member['id'],'member');
						$this->msg("Novo membro activado membro!! ".$email);
						$this->title = "";
					}
					
					$this->reroute('coalition','memberslist');
					
					/*$this->notifyMods("New account to be approved $name","Please <a href='".$this->f3->get('url')."/en/coalition/member/".$this->f3->get('POST.slug')."?ltoken=%login_token%'>login here</a> to the website to review the details of the new member and activate the new account!");
					
					$this->sendMail("$name <$email>",'Your account is awaiting moderation, please wait for the activation e-mail sent by our team.','Hello,<br> we received your details, please wait for the activation of your account.');
					
					$this->reroute('coalition','thankyou');*/
					
					
					
				}else{
					$this->error($this->getTranslation('error_submission'));
				}
			}
		}
		
		$this->f3->set('section_subtitle',"Active coalition members");
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function edit()
	{
		$user_id = $this->f3->get('SESSION.user.id');
		
		
		
		if($this->Members->isAdmin() &&  $this->f3->exists('PARAMS.p1')){
			$user_id = $this->f3->get('PARAMS.p1');
		}
		
		if($this->f3->exists('POST.email')){
			$this->f3->set('POST.id',$user_id);
			if(!$this->Members->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Members->edit()){
					$this->msg($this->getTranslation('form_ok'));
					$this->reroute('coalition','memberslist');
				}else{
					$this->error($this->getTranslation('form_error'));
				}
			}
		}
		
		else{
			// $this->log('copying to post....');
			$this->copyToPost($this->Members->getUser($user_id));
		}
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function links()
	{
		// $this->f3->set('SESSION.member_slug',$this->f3->get('POST.slug'));
		$this->f3->set('section_title',"Actions aggregator");
		
		$this->f3->set("links",$this->MembersSocial->paginate($this->f3->get('PARAMS.p1'),10,'created','DESC',"WHERE member_id = ".$this->f3->get('SESSION.user.id')));
		$this->f3->set("social_types",$this->MembersSocial->getTypes());
	}
	
	
	
	public function login()
	{
		/*if($_SERVER['HTTP_HOST'] != 'housing.localhost'){
			
			if (empty($_SERVER['HTTPS'])) {
					
					$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					header('location: https://'.$url);
					exit();
			}
		}*/
		$bfroute = $this->f3->get('SESSION.lastroute');
		
		
		
		
		if($this->f3->exists('POST.email')){
			
			
			if(!$this->Members->login())
			{
				$email = $this->f3->get('POST.email');
				// die('ERRO LOGIN');
				
				$this->f3->set('SESSION.error_login','truue');
				$this->f3->set('email',$email);
				return;
				// $this->reroute('coalition','login');
			}
			
			// print_r($_SESSION);
			// die('BEM VINDO');
			$this->msg('Welcome '.$this->f3->get('SESSION.user.name').'!');
			// https://housingnotprofit.org/en/coalition/member
			// $this->f3->reroute('/'.$this->f3->get('lang_set').'/coalition/member/'.$this->f3->get('SESSION.user.slug'));
			// print_r($_SESSION);
			// die('SEM MAIL?');
		}
		
		
		if($this->Members->isAuthenticated()){
			// print_r($this->f3->get('SESSION'));
			// $this->f3->reroute('/'.$this->f3->get('lang_set').'/coalition/member/'.$this->f3->get('SESSION.user.slug'));
			$this->f3->reroute('/');
			// die('buuh');
		}
		// die('SEM MAIL?');
	}
	public function logout()
	{
		$this->f3->clear('SESSION.login');
		$this->f3->clear('SESSION.user');
		
		$this->f3->reroute('/');
	}
	
	public function members_admin()
	{
		$this->f3->set('members',$this->Members->paginate($this->f3->get('PARAMS.p1'),10,'name','ASC'));
	}
	
	public function members()
	{
		$this->layout = 'blank';
		$this->Members->getPages();
	}
	
	public function member()
	{
		$sql_published = "members.published = 1";
		$slug = $this->f3->get('PARAMS.p1');
		if($this->Members->isAdmin() || $slug == $this->f3->get('SESSION.user.slug')){
			$sql_published = "1";
		}
		
		$sql = "WHERE slug like '%".$slug."%' AND $sql_published ";
		$member = $this->Members->getRow($sql);
		
		if(empty($member)){
			$this->error('Member not found');
			$this->reroute('coalition','memberslist');
		}
		
		// die('fuu');
		$this->f3->set('member',$member);
		$member_id = $member['id'];
		
		// echo $member_id;
		$this->f3->set('aggregations',$this->Aggregator->getRss(1,$member_id));
		$this->f3->set('aggregations_social',$this->Aggregator->getSocial(1,$member_id));
		
		$this->f3->set('hidememberinfo',true);
	}
	
	function thankyou()
	{
               // $this->layout = 'default_temp';
               $this->f3->set('section_title','Confirme o seu registo!');
               $this->title = 'Obrigado, pelo registo, aguarde o mail com a confirmação!';
		
	}
	
	function publish()
	{
		if(!$this->f3->exists('PARAMS.p1') || !$this->Members->update("id = ".$this->f3->get('PARAMS.p1'),array("published" => 1))){
			$this->error($this->getTranslation('form_error'));
			
		}else{
			$this->msg($this->getTranslation('form_ok'));
		}
		
		
		$this->goback();
	}
	
	function activate()
	{
		if(!$this->Members->activate($this->f3->get('PARAMS.p1'))){
			$this->error($this->getTranslation('form_error'));
		}else{
			//$this->sendMail($member['email'],'Your account has been approved','Hello,<br>You can view your <a href="'.$this->f3->get('url').'/en/coalition/member/'.$member['slug'].'">member page</a><br> Your account has been approved, you can now login to our website!');
	
			$this->msg($this->getTranslation('form_ok'));
		}
		
		
		$this->goback();
	}
	function hide()
	{
		if(!$this->f3->exists('PARAMS.p1') || !$this->Members->update("id = ".$this->f3->get('PARAMS.p1'),array("published" => 0))){
			$this->error($this->getTranslation('form_error'));
		}else{
			$this->msg($this->getTranslation('form_ok'));
		}
		
		$this->goback();
	}
	
	function remove()
	{
		if(!$this->f3->exists('PARAMS.p1')){
			$this->error($this->getTranslation('form_error'));
		}else
		{
			$this->Members->delete($this->f3->get('PARAMS.p1'));
			$this->msg($this->getTranslation('form_ok'));
		}
		
		$this->reroute('coalition');
	}
	
	function linkedit()
	{
		$member_id = $this->f3->get('SESSION.user.id');
		
		$id = $this->f3->get('PARAMS.p1');
		
		if(!$this->Members->isAdmin()){ 
			$row = $this->MembersSocial->getRow("WHERE id = $id AND member_id = $member_id");
			if(empty($row))
			{
				$this->reroute('coalition','links');
			}
		}
		
		$this->f3->set('POST.id',$id);
		if($this->f3->exists('POST.url')){
			if(!$this->MembersSocial->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->MembersSocial->edit()){
					$this->msg($this->getTranslation('form_ok'));
					$this->reroute('coalition','links');
				}else{
					$this->error($this->getTranslation('form_error'));
				}
			}
		}else{
			$this->log('copying to post....');
			$this->copyToPost($this->MembersSocial->getRow("WHERE id = $id"));
		}
		
		$this->f3->set("available_langs",$this->languagesCombo());
		$this->f3->set("types",$this->MembersSocial->types);
	}
	
	function map()
	{
		$this->layout = 'blank';
		$this->f3->set('map_users',json_encode($this->Members->getMap()));
	}
	
	function error404()
	{
	}
	
	function recover()
	{
		$this->f3->clear('SESSION.error_login');
		if($this->f3->exists('POST.email')){
			$email = $this->f3->get('POST.email');
			$pass = $this->Members->newpassword($email);
			$this->sendMail($email,'Your account has been recovered','Hello,<br>Here is a new password generated for you: '.$pass);
			$this->msg('Please check your e-mail.');
			$this->reroute('coalition','login');
			
			
		}
		
		
	}
	
	function onelinkrecovery()
	{
		$email = $this->f3->get('GET.email');
		
		$member = $this->Members->getRow("WHERE email like '$email' ");
		
		
		if(empty($member)){
			$this->f3->reroute('/');
			return;
		}
		
		$name = $member['name'];
		$this->oneClickMail($member['id'],"$name <$email>","Your ".$this->f3->get('url')." login request","
			<h2>
			Use this link to securely login to your account @".$member['slug'].".<br><br>
			<h1>
			<a href='".$this->f3->get('url')."/en/coalition/member/".$member['slug']."?ltoken=%login_token%'>login here</a>.
			</h1>
			</h2>
		");
	}
	
	
}
