<?php

namespace App\Controllers;

class Articles extends ControllerApp{
	
	public $translate = true;
	protected $models = array("Articles","Members");
	
	public $title = '';
	function __construct()
	{
		
		
		
		parent::__construct();
		
		$this->f3->set('SESSION.lastseen',time());
		$this->f3->set('SESSION.ip',$_SERVER['REMOTE_ADDR']);
		
	}
	
	function index()
	{
		// $this->reroute('coalition');
		// if(($this->f3->exists('LANGS') || $this->f3->exists('LANG') ) && !$this->f3->exists('PARAMS.lang')){
		// 	$this->f3->reroute('/'.$this->f3->get('LANG').'/');
		// }
		
		// $this->f3->set('articles',$this->Articles->paginate(1,2,'created','DESC','WHERE published = 1 AND original_article is null'));
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		
		$this->f3->set('articles',$this->Articles->paginate(1,6,'created','DESC',"WHERE $sql_pub AND original_article is null"));
	}
	
	function articles()
	{
		$sql_pub = "published = 1";
		if($this->Members->isAdmin()){
			$sql_pub = "1";
		}
		$this->layout = 'blank';
		$this->f3->set('articles',$this->Articles->paginate($this->f3->get('PARAMS.p1'),6,'created','DESC',"WHERE $sql_pub AND original_article is null"));
	}
	
	function thankyou()
	{
	}
	function publish()
	{
		if($this->f3->exists('POST.title')){
			if(!$this->Articles->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Members->isAdmin()){
					$this->f3->set('POST.published',1);
				}else{
					$this->f3->set('POST.published',0);
				}
				if($this->Articles->save()){
					if(!$this->Members->isAdmin()){
						$this->notifyMods("A new article has been submitted, please approve it!","
							Please <a href='".$this->f3->get('url')."/en/articles/view/".$this->f3->get('POST.slug')."?ltoken=%login_token%'>login here</a> to the website to review the details of the new article and publish it!");
						// die('FUU');
						$this->msg($this->getTranslation('submission_ok'));
						$this->reroute('articles','thankyou');
					}else{
						$this->msg("The article is now published!");
						$this->reroute('articles','view/'.$this->f3->get('POST.slug'));
					}
				}else{
					$this->error($this->getTranslation('submission_error'));
				}
			}
		}
		
		if($this->Members->isAuthenticated()){
			
			echo "OI";
			$user = $this->f3->get('SESSION.user');
			$this->f3->set('POST.author_name',$user['name']);
			$this->f3->set('POST.author_email',$user['email']);
			$this->f3->set('POST.author_img',$user['avatar']);
		}
		$this->f3->set("available_langs",$this->languagesCombo());
	}
	
	
	function publishit()
	{
		$id = "";
		if($this->f3->exists('PARAMS.p1')){
			$id = $this->f3->get('PARAMS.p1');
		}
		 if(!$this->Articles->update("id = ".$id,array("published" => 1))){
			$this->error($this->getTranslation('form_error'));
			
		}else{
			$this->Members->newAction("new article published",$id,'article');
			$this->msg($this->getTranslation('form_ok'));
		}
		$article = $this->Articles->getRow('WHERE id = '.$id);
		$this->reroute('articles','view/'.$article['slug']);
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
	
	function addtranslation()
	{
		if($this->f3->exists('POST.title')){
			
			$this->f3->set('POST.published',0);
			if($this->Articles->save()){
				//enviar email... para moderadores...
				
				if($this->Members->isAdmin()){
					
					$toTranslate['id'] = $this->f3->get('POST.original_article');
					$toTranslate['i18n'] = $this->f3->get('POST.original_language');
					$toTranslate['title'] = $this->f3->get('POST.title');
					$toTranslate['text'] = $this->f3->get('POST.text');
					$this->f3->clear('POST');
					
					$this->copyToPost($toTranslate);
					
					$this->Articles->edit();
					$this->msg($this->getTranslation('submission_ok'));
					$this->reroute('articles','view/'.$this->f3->get('PARAMS.p1'));
				}else{
					$this->msg('Your article translation was sent, please wait for it to be accepted!');
					$this->notifyMods("A new article translation has been submitted, please approve it!","
							Please <a href='".$this->f3->get('url')."/en/articles/view/".$this->f3->get('PARAMS.p1')."?ltoken=%login_token%'>login here</a> to the website to review the details of the new article and publish it!");
				}
				
				
				
			}else{
				$this->error('Error saving article translating...');
			}
			$this->reroute('articles','view/'.$this->f3->get('PARAMS.p1'));
		}
		$article = $this->Articles->getRow("WHERE slug like '%".$this->f3->get('PARAMS.p1')."%' ");
		$this->f3->set('article',$article);
		
		$available_langs = $this->languagesCombo();
		
		// $translated_langs = $this->Articles->getTranslatedLangs($article['id'],$available_langs);
		$untraslanted_langs = $this->Articles->getUntranslatedLangs($article['id'],$available_langs);
		
		$this->f3->set("available_langs",$untraslanted_langs);
		$this->f3->set("all_langs",$available_langs);
		
		// $this->f3->set("to_approve_translations",)
	}
	
	function publishtranslation()
	{
		
		
		
		
		$translated_article = $this->Articles->getRow("WHERE id = ".$this->f3->get('PARAMS.p1'));
		$article = $this->Articles->getRow("WHERE id = ".$translated_article['original_article']);
		$this->f3->set('article',$article);
		
		$this->f3->set("available_langs",$this->languagesCombo());
		
		// $this->f3->set('POST.id',$article['original_article']);
		// $this->f3->set('POST.i18n',$article['original_language']);
		// $this->f3->set('POST.summary',$article['summary']);
		// $this->f3->set('POST.author_name',$article['author_name']);
		// $this->f3->set('POST.author_email',$article['author_email']);
		// $this->f3->set('POST.title',$article['title']);
		// $this->f3->set('POST.text',$article['text']);
		
		
		if($this->f3->exists('POST.i18n')){
			$this->Articles->edit();
			$this->msg($this->getTranslation('submission_ok'));
			$this->reroute('articles','view/'.$article['slug']);
		}
		
		$this->copyToPost($translated_article);
		// $this->msg($this->getTranslation('submission_ok'));
	}
	
	function delete(){
		$this->Articles->delete($this->f3->get('PARAMS.p1'));
		$this->reroute('articles','index');
	}
	
	function deletetranslation()
	{
		$id = $this->f3->get('PARAMS.p1');
		$lang = $this->f3->get('PARAMS.p2');
		$this->db->query("DELETE FROM i18n_translations WHERE field_id = $id AND table_name = 'articles' AND language = $lang");
	}
	
	
	function edit()
	{
		$this->f3->set('available_langs',$this->languagesCombo());
		$article_id = $this->f3->get('PARAMS.p1');
		
		if($this->f3->exists('POST.title')){
			$this->f3->set('POST.id',$article_id);
			if(!$this->Articles->validate()){
				$this->error($this->getTranslation('validation_error'));
			}else{
				if($this->Articles->edit()){
					$this->msg($this->getTranslation('form_ok'));
					$article = $this->Articles->getRow("where id = ".$article_id);
					
					$this->reroute('articles','view/'.$article['slug']);
				}else{
					$this->error($this->getTranslation('form_error'));
				}
			}
		}
		
		else{
			$this->copyToPost($this->Articles->getRow("WHERE id = ".$article_id));
		}
		
	}
	
	function hide()
	{
		if(!$this->f3->exists('PARAMS.p1') || !$this->Articles->update("id = ".$this->f3->get('PARAMS.p1'),array("published" => 0))){
			$this->error($this->getTranslation('form_error'));
		}else{
			$this->msg($this->getTranslation('form_ok'));
		}
		
		$this->goback();
	}
	
}
