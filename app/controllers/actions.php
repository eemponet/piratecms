<?php

namespace App\Controllers;

class Actions extends ControllerApp{
	
	public $translate = true;
	protected $models = array("Actions","Members","Aggregator");
	
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
		$this->f3->set('actions',$this->Actions->paginate(1,1000,'`when`','ASC',"WHERE tipo = 'antifrack' AND `when` > NOW() OR until > NOW() "));
		// $this->actions();
		// $this->
		// $this->layout = 'default';
	}
	
	function upcoming()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$actions = $this->Actions->paginate($this->f3->get('PARAMS.p1'),40,'`when`','ASC',"WHERE $sql_pub AND (`when` > NOW() OR `until` > NOW()) ");
		
		$this->f3->set('actions',$actions);
		$this->f3->set('map_users',json_encode($this->Actions->getMap($actions)));
		// $this->map();
	}
	
	function all()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$actions = $this->Actions->paginate($this->f3->get('PARAMS.p1'),40,'`when`','ASC',"WHERE $sql_pub ");
		
		$this->f3->set('actions_all',$actions);
		// $this->f3->set('map_users',json_encode($this->Actions->getMap($actions)));
		// $this->map();
	}
	
	function past()
	{
		
		$this->f3->set('actions',$this->Actions->paginate(1,1000,'`when`','ASC',"WHERE  `when` < NOW() "));
		/*
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$actions = $this->Actions->paginate($this->f3->get('PARAMS.p1'),40,'`when`','DESC',"WHERE $sql_pub AND (`when` < NOW()  AND `until` < NOW())");
		
		if($this->f3->exists("actions")){
			$actions_1 = $this->f3->set('actions',$actions);
			$actions = array_merge($actions,$actions_1);
			$this->f3->set('past_actions',$actions);
		}else{
			$this->f3->set('actions',$actions);
		}
		
		if($this->f3->exists("map_users")){
			$this->f3->set('past_actions_map_users',json_encode($this->Actions->getMap($actions,'images/menu_actions_past.png')));
		}else{
			$this->f3->set('map_users',json_encode($this->Actions->getMap($actions)));
		}*/
		
		
		
		
	}
	
	function etapa1()
	{
		$this->f3->set('actions',$this->Actions->paginate(1,1000,'`when`','ASC',"WHERE tipo = 'etapa1' AND (`when` > NOW() OR until > NOW()) "));
	}
	
	function etapa2()
	{
		$this->f3->set('actions',$this->Actions->paginate(1,1000,'`when`','ASC',"WHERE tipo = 'etapa2' AND (`when` > NOW() OR until > NOW() )"));
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
		
		$actions = $this->Actions->paginate($this->f3->get('PARAMS.p1'),40,'`when`','DESC',"WHERE $sql_pub");
		$this->f3->set('actions',$actions);
		$this->f3->set('map_users',json_encode($this->Actions->getMap($actions)));
		*/
	}
	
	function edit()
	{
		// $this->f3->set('available_langs',$this->languagesCombo());
		$article_id = $this->f3->get('PARAMS.p1');
		
		if($this->f3->exists('POST.when')){
			$this->f3->set('POST.id',$article_id);
			if(!$this->Actions->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Actions->edit()){
					$this->msg($this->getTranslation('form_ok'));
					$article = $this->Actions->getRow("where id = ".$article_id);
					
					$this->reroute('actions');
				}else{
					$this->error($this->getTranslation('form_error'));
				}
			}
		}
		
		else{
			$this->copyToPost($this->Actions->getRow("WHERE id = ".$article_id));
		}
		
	}
	function publish()
	{
		$this->f3->set('POST.tipo','antifrack');
		if($this->f3->exists('POST.when')){
			if(!$this->Actions->validate()){
				
				$this->error($this->getTranslation('validation_error'));
			}else{
				
				if($this->Actions->save()){
					
					$this->msg("The event is now published!");
					if($this->f3->get('POST.tipo') == 'antifrack'){
						$this->reroute('actions');
					}else{
						$this->reroute('actions','etapa1');
					}
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
		}
		
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function addEtapa1()
	{
		$this->f3->set('POST.tipo','etapa1');
		if($this->f3->exists('POST.when')){
			if(!$this->Actions->validate()){
				
				$this->error($this->getTranslation('validation_error'));
			}else{
				
				if($this->Actions->save()){
					
					$this->msg("The event is now published!");
					$this->reroute('actions');
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
		}
		
		
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	function addEtapa2()
	{
		$this->f3->set('POST.tipo','etapa2');
		if($this->f3->exists('POST.when')){
			if(!$this->Actions->validate()){
				
				$this->error($this->getTranslation('validation_error'));
			}else{
				
				if($this->Actions->save()){
					
					$this->msg("The event is now published!");
					$this->reroute('actions');
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
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
		$event = $this->Actions->getRow("WHERE slug like '".$slug."'");
		if(empty($event)){
			$event = $this->Actions->getRow("WHERE id = ".$slug."");
			$this->reroute('actions','view/'.$event['slug']);
		}
		// $event['share_url'] = '/expando/add/index.htm?u='.$this->f3->get('url').'/actions/view/'.$event['id'].'&t='.urlencode($event['name']);
		$action_id = $event['id'];
		$this->f3->set('aggregations_action',$this->Aggregator->getTwits(1,"aggregator.action_id = ".$action_id));
		
		$this->f3->set('event',$event);
		
		$this->f3->set('event.translated',$this->Actions->getTranslatedLangs($event['id']));
		
		$this->f3->set('pending_translations',$this->Actions->getPendingTranslations($event['id'],$this->languagesCombo()));
		
		
	}
	
	function map()
	{
		$this->layout = 'blank';
		$this->f3->set('map_users',json_encode($this->Actions->getMap()));
	}
	
	function addtranslation()
	{
		if($this->f3->exists('POST.name')){
			$this->f3->set('POST.published',0);
			
			if($this->Actions->save()){
				//enviar email... para moderadores...
				
				if($this->Members->isAdmin()){
					
					$toTranslate['id'] = $this->f3->get('POST.original_article');
					$toTranslate['i18n'] = $this->f3->get('POST.original_language');
					$toTranslate['details'] = $this->f3->get('POST.details');
					$toTranslate['name'] = $this->f3->get('POST.name');
					$this->f3->clear('POST');
					
					$this->copyToPost($toTranslate);
					
					$this->Actions->edit();
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
		
		$event = $this->Actions->getRow("WHERE id = ".$this->f3->get('PARAMS.p1')." ");
		$this->f3->set('event',$event);
		
		$available_langs = $this->languagesCombo();
		
		$untraslanted_langs = $this->Actions->getUntranslatedLangs($event['id'],$available_langs);
		
		$this->f3->set("available_langs",$untraslanted_langs);
		$this->f3->set("all_langs",$available_langs);
		
	}
	
	function publishtranslation()
	{
		
		$translated_article = $this->Actions->getRow("WHERE id = ".$this->f3->get('PARAMS.p1'));
		$event = $this->Actions->getRow("WHERE id = ".$translated_article['original_article']);
		$this->f3->set('event',$event);
		
		$this->f3->set("available_langs",$this->languagesCombo());
		
		if($this->f3->exists('POST.i18n')){
			$this->Actions->edit();
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
		 if(!$this->Actions->update("id = ".$id,array("published" => 1))){
			$this->error($this->getTranslation('form_error'));
			
		}else{
			$this->Members->newAction("new event published",$id,'event');
			$this->msg($this->getTranslation('form_ok'));
		}
		$event = $this->Actions->getRow('WHERE id = '.$id);
		$this->reroute('actions','view/'.$event['id']);
	}
	function delete(){
		$this->Actions->delete($this->f3->get('PARAMS.p1'));
		$this->goback();
	}
	
	function deletetranslation()
	{
		$id = $this->f3->get('PARAMS.p1');
		$lang = $this->f3->get('PARAMS.p2');
		$this->db->exec("DELETE FROM i18n_translations WHERE field_id = $id AND table_name = 'Actions' AND language like '$lang'");
		$this->goback();
	}
	
	function hide()
	{
		if(!$this->f3->exists('PARAMS.p1') || !$this->Actions->update("id = ".$this->f3->get('PARAMS.p1'),array("published" => 0))){
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
