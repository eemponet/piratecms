<?php

namespace App\Controllers;

class Campaigns extends ControllerApp{
	
	public $translate = true;
	protected $models = array("Campaigns","Actions","Members","Aggregator");
	
	public $title = '';
	function __construct()
	{
		parent::__construct();
		
		$this->f3->set('SESSION.lastseen',time());
		$this->f3->set('SESSION.ip',$_SERVER['REMOTE_ADDR']);
		
	}
	
	function index()
	{
		// $sql_pub = "published = 1";
		// if($this->Members->isAdmin()){
			// $sql_pub = "1";
		// }
		// $this->f3->set('actions',$this->Events->paginate(1,2,'`when`','ASC',"WHERE $sql_pub AND `when` > NOW()"));
		$this->campaigns();
		$this->layout = 'default';
	}
	
	function upcoming()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$campaigns = $this->Campaigns->paginate($this->f3->get('PARAMS.p1'),40);
		
		$this->f3->set('campaigns',$campaigns);
		
		$actions = $this->Actions->paginate($this->f3->get('PARAMS.p1'),40,'`when`','ASC',"WHERE $sql_pub AND (`when` > NOW() OR `until` > NOW()) ");
		$this->f3->set('map_users',json_encode($this->Actions->getMap($actions)));
		// $this->map();
	}
	
	function all()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$campaigns = $this->Campaigns->paginate($this->f3->get('PARAMS.p1'),40,'id','DESC',"WHERE $sql_pub ");
		
		$this->f3->set('campaigns',$campaigns);
		$this->f3->set('map_users',json_encode($this->Events->getMap($actions)));
		// $this->map();
	}
	
	function past()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$actions = $this->Campaigns->paginate($this->f3->get('PARAMS.p1'),40,'`when`','DESC',"WHERE $sql_pub AND (`when` < NOW()  AND `until` < NOW())");
		
		if($this->f3->exists("actions")){
			$actions_1 = $this->f3->set('actions',$actions);
			$actions = array_merge($actions,$actions_1);
			$this->f3->set('past_actions',$actions);
		}else{
			$this->f3->set('actions',$actions);
		}
		
		if($this->f3->exists("map_users")){
			// $this->f3->set('past_actions_map_users',json_encode($this->Events->getMap($actions,'images/menu_actions_past.png')));
		}else{
			// $this->f3->set('map_users',json_encode($this->Events->getMap($actions)));
		}
		
		
		
		
	}
	
	function actions()
	{
		$this->all();
		$this->upcoming();
		$this->past();
		/*$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$actions = $this->Events->paginate($this->f3->get('PARAMS.p1'),40,'`when`','DESC',"WHERE $sql_pub");
		$this->f3->set('actions',$actions);
		$this->f3->set('map_users',json_encode($this->Events->getMap($actions)));
		*/
	}
	
	function edit()
	{
		$this->f3->set('available_langs',$this->languagesCombo());
		$article_id = $this->f3->get('PARAMS.p1');
		
		if($this->f3->exists('POST.name')){
			$this->f3->set('POST.id',$article_id);
			if(!$this->Events->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Events->edit()){
					$this->msg($this->getTranslation('form_ok'));
					$article = $this->Events->getRow("where id = ".$article_id);
					
					$this->reroute('actions','view/'.$article['slug']);
				}else{
					$this->error($this->getTranslation('form_error'));
				}
			}
		}
		
		else{
			$this->copyToPost($this->Events->getRow("WHERE id = ".$article_id));
		}
		
	}
	function publish()
	{
		if($this->f3->exists('POST.name')){
			if(!$this->Events->validate()){
				
				
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Members->isAuthenticated()){
					$this->f3->set('POST.published',1);
				}else{
					$this->f3->set('POST.published',0);
				}
				if($this->Events->save()){
					if(!$this->Members->isAuthenticated()){
						$this->notifyMods("A new event has been submitted, please approve it!","
							Please <a href='".$this->f3->get('url')."/en/actions/view/".$this->db->lastId()."?ltoken=%login_token%'>login here</a> to the website to review the details of the new article and publish it!");
						
						$this->msg($this->getTranslation('submission_ok'));
						$this->reroute('actions','thankyou');
					}else{
						$this->msg("The event is now published!");
						$this->reroute('actions','view/'.$this->db->lastId());
					}
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
		}
		
		if($this->Members->isAuthenticated()){
			$user = $this->f3->get('SESSION.user');
			$this->f3->set('POST.author_name',$user['name']);
			$this->f3->set('POST.author_email',$user['email']);
			$this->f3->set('POST.author_img',$user['avatar']);
		}
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function thankyou()
	{
	}
	
	function view()
	{
		
		$slug = $this->f3->get('PARAMS.p1');
		
		$this->f3->set("available_langs",$this->languagesCombo());
		$event = $this->Events->getRow("WHERE slug like '".$slug."'");
		if(empty($event)){
			$event = $this->Events->getRow("WHERE id = ".$slug."");
			$this->reroute('actions','view/'.$event['slug']);
		}
		// $event['share_url'] = '/expando/add/index.htm?u='.$this->f3->get('url').'/actions/view/'.$event['id'].'&t='.urlencode($event['name']);
		$action_id = $event['id'];
		$this->f3->set('aggregations_action',$this->Aggregator->getTwits(1,"aggregator.action_id = ".$action_id));
		
		$this->f3->set('event',$event);
		
		$this->f3->set('event.translated',$this->Events->getTranslatedLangs($event['id']));
		
		$this->f3->set('pending_translations',$this->Events->getPendingTranslations($event['id'],$this->languagesCombo()));
		
		
	}
	
	function map()
	{
		$this->layout = 'blank';
		$this->f3->set('map_users',json_encode($this->Events->getMap()));
	}
	
	function addtranslation()
	{
		if($this->f3->exists('POST.name')){
			$this->f3->set('POST.published',0);
			
			if($this->Events->save()){
				//enviar email... para moderadores...
				
				if($this->Members->isAdmin()){
					
					$toTranslate['id'] = $this->f3->get('POST.original_article');
					$toTranslate['i18n'] = $this->f3->get('POST.original_language');
					$toTranslate['details'] = $this->f3->get('POST.details');
					$toTranslate['name'] = $this->f3->get('POST.name');
					$this->f3->clear('POST');
					
					$this->copyToPost($toTranslate);
					
					$this->Events->edit();
					$this->msg($this->getTranslation('submission_ok'));
					$this->reroute('actions','view/'.$this->f3->get('PARAMS.p1'));
				}else{
					$this->msg('Your event was sent, please wait for it to be accepted!');
					$this->notifyMods("A new event translation has been submitted, please approve it!","
							Please <a href='".$this->f3->get('url')."/en/actions/view/".$this->f3->get('PARAMS.p1')."?ltoken=%login_token%'>login here</a> to the website to review the details of the new article and publish it!");
				}
			}else{
				$this->error('Error submitting event translation.');
			}
			$this->reroute('actions','view/'.$this->f3->get('PARAMS.p1'));
		}
		
		$event = $this->Events->getRow("WHERE id = ".$this->f3->get('PARAMS.p1')." ");
		$this->f3->set('event',$event);
		
		$available_langs = $this->languagesCombo();
		
		$untraslanted_langs = $this->Events->getUntranslatedLangs($event['id'],$available_langs);
		
		$this->f3->set("available_langs",$untraslanted_langs);
		$this->f3->set("all_langs",$available_langs);
		
	}
	
	function publishtranslation()
	{
		
		$translated_article = $this->Events->getRow("WHERE id = ".$this->f3->get('PARAMS.p1'));
		$event = $this->Events->getRow("WHERE id = ".$translated_article['original_article']);
		$this->f3->set('event',$event);
		
		$this->f3->set("available_langs",$this->languagesCombo());
		
		if($this->f3->exists('POST.i18n')){
			$this->Events->edit();
			$this->msg($this->getTranslation('submission_ok'));
			$this->reroute('actions','view/'.$event['id']);
		}
		
		$this->copyToPost($translated_article);
		// $this->msg($this->getTranslation('submission_ok'));
	}
	
	function publishit()
	{
		$id = "";
		if($this->f3->exists('PARAMS.p1')){
			$id = $this->f3->get('PARAMS.p1');
		}
		 if(!$this->Events->update("id = ".$id,array("published" => 1))){
			$this->error($this->getTranslation('form_error'));
			
		}else{
			$this->Members->newAction("new event published",$id,'event');
			$this->msg($this->getTranslation('form_ok'));
		}
		$event = $this->Events->getRow('WHERE id = '.$id);
		$this->reroute('actions','view/'.$event['id']);
	}
	function delete(){
		$this->Events->delete($this->f3->get('PARAMS.p1'));
		$this->reroute('actions','index');
	}
	
	function deletetranslation()
	{
		$id = $this->f3->get('PARAMS.p1');
		$lang = $this->f3->get('PARAMS.p2');
		$this->db->exec("DELETE FROM i18n_translations WHERE field_id = $id AND table_name = 'events' AND language like '$lang'");
		$this->goback();
	}
	
	function hide()
	{
		if(!$this->f3->exists('PARAMS.p1') || !$this->Events->update("id = ".$this->f3->get('PARAMS.p1'),array("published" => 0))){
			$this->error($this->getTranslation('form_error'));
		}else{
			$this->msg($this->getTranslation('form_ok'));
		}
		
		$this->goback();
	}
	/*
	function publish()
	{
		if($this->f3->exists('POST.title')){
			if(!$this->Articles->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Articles->save()){
					$this->msg($this->getTranslation('submission_ok'));
					// $this->f3->reroute('/');
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
		}
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	
	
	function translate()
	{
	}
	
	function comment()
	{
	}
	
	function share()
	{
	}
	
	function beforeroute(){
		parent::beforeroute();
	}
	
	function view()
	{
		$this->f3->set("available_langs",$this->languagesCombo());
		
		$article = $this->Articles->getRow("WHERE slug like '%".$this->f3->get('PARAMS.p1')."%' ");
		$this->f3->set('article',$article);
		$this->f3->set('article.translated',$this->Articles->getTranslatedLangs($article['id']));
		
		$this->f3->set('pending_translations',$this->Articles->getPendingTranslations($article['id'],$this->languagesCombo()));
		
		
	}
	
	
	
	
	
	
	*/
}
