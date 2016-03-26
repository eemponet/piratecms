<?php


namespace App\Models;
class Articles extends Model {
	
	public $tableName = 'articles';
	public $translated_fields = array('author_name','author_email','title','text');
	public $force_translation = false;
	public $destination_dir = 'images/articles/';
	
	public $hasOne = array(
			array("Model" => "Members","referenceKey" => "member_id","foreignKey" => "id")
			);
	public function __construct() {
		
		parent::__construct();
		
		$this->validations = array(
				
				// array('author_name' => array('required' => true, 'minlength' => 10)),
				// array('author_email' => array('required' => true,'email' => true)),
				array('title' => array('required' => true, 'minlength' => 5)),
				// array('summary' => array('required' => true, 'minwords' => 50, 'maxwords' => 200)),
				array('text' => array('required' => true, 'minwords' => 20)),
				array('original_language' => array('required' => true)),
				// array('author_img' => array('image' => true, 'filesize' => 500000)),
				array('img' => array('image' => true, 'filesize' => 500000))
				);
		
		
		
	}
	
	public function validate(){
		$this->saveFile('img');
		// return true;
		return parent::validate();
	}
	public function beforeSave(){
		
		if(!$this->validate())
		{
			return false;
		}
		
		if(!$this->f3->exists('POST.original_article')){
			$this->saveFile('author_img','jpg',300,300);
			
			
			$slug = $this->slugify($this->f3->get('POST.title'));
			$this->f3->set('POST.slug',$slug);
			
			$slug_duplicate = $this->getRow("WHERE slug like '".$slug."'");
			if(!empty($slug_duplicate)){
				$slug .= uniqid();
				$this->f3->set('POST.slug',$slug);
			}
		}
		
		if($this->f3->exists('SESSION.user')){
			$this->f3->set('POST.member_id',$this->f3->get('SESSION.user.id'));
		}
		
		
		// $author_img = $this->f3->get('FILES.author_img');
		
		// $img = $this->f3->get('FILES.img');
		
		// return false;
		// $imagem = $this->f3->get('FILES.nova_imagem');
		
		// if(!empty($imagem['name'])){
			
		// 	$file = $this->saveFile('nova_imagem',$this->dst_dir );
		// 	if(!$file){
		// 		$this->f3->set('error_msg','Erro a subir imagem...');
		// 		return false;
		// 	}
		// 	$this->f3->set('POST.imagem',$this->dst_dir.$file);
		// }
		// else{
		// 	$this->f3->clear('POST.imagem');
		// }
		
		// return true;
		
		return parent::beforeSave();
	}
	
	
	function getRow($conds = '',$select = '*',$showQuery = false)
	{
		$res = $this->getRows($conds.' LIMIT 1',$select,$showQuery);
		if(isset($res[0]))
		{
			return $res[0];
		}
		return array();
	}
	
	function getRows($conds = '',$select = '*',$showQuery = false)
	{
		$rows = parent::getRows($conds,$select,$showQuery);
		foreach($rows as $key => $valu){
			if(isset($rows[$key]['slug'])){
				$rows[$key]['share_url'] = '/expando/add/index.htm?u='.$this->f3->get('url').'/articles/view/'.$rows[$key]['slug'].'&t='.urlencode($rows[$key]['title']);
			}
			if(isset($rows[$key]['text'])){
				// $rows[$key]['summary'] = getSummary($rows[$key]['text'],1100);
				$rows[$key]['summary'] = $rows[$key]['text'];
			}
		}
		
		return $rows;
	}
		/*
		$rows = parent::getRows($conds,$select,$showQuery);
		foreach($rows as $key => $row)
		{
			$translated_langs = $this->getTranslatedLangs($row['id']);
			// print_r($translated_langs);
			// die(1);
			if(!empty($translated_langs)){
				$rows[$key]['translated'] = $translated_langs;
			}
		}
		return $rows;
	}*/
	
	function getTranslatedLangs($id)
	{
		$langs_rt = array();
		$langs = $this->db->getRows("i18n_translations","WHERE table_name = 'articles' AND field_id = $id ","distinct language");
		
		foreach($langs as $key => $val)
		{
			$langs_rt[] = $val['language'];
		}
		// $row = $this->getRow("WHERE id = $id");
		// $langs_rt[] = $row['original_language'];
		return $langs_rt;
	}
	
	// function getUntraslantedLangs($id)
	// {
	// 	$langs_rt = array();
	// 	$langs = $this->db->getRows("articles","WHERE (id = $id OR original_article = $id )AND published = 1","distinct original_language");
		
	// 	foreach($langs as $key => $val)
	// 	{
	// 		$langs_rt[] = $val;
	// 	}
		
	// 	return $langs_rt;
	// }
	
	function getUntranslatedLangs($id,$available_langs)
	{
		$translatedlangs = $this->getTranslatedLangs($id);
		
		foreach($available_langs as $key => $val){
			if(in_array($key,$translatedlangs))
			{
				unset($available_langs[$key]);
			}
		}
		$article = $this->getRow("WHERE id = $id");
		unset($available_langs[$article['original_language']]);
		
		return $available_langs;
	}
	
	function getPendingTranslations($id,$available_langs)
	{
		
		$pending_moderation_langs = array();
		
		$approvedtranslatedlangs = $this->getTranslatedLangs($id);
		
		
		$translated_langs = $this->getRows("WHERE original_article = $id","original_language,id");
		
		foreach($translated_langs as $key => $val)
		{
			if(!in_array( $val['original_language'],$approvedtranslatedlangs)){
				$pending_moderation_langs[]= $val;
			}
			
		}
		
		return $pending_moderation_langs;
	}
}


