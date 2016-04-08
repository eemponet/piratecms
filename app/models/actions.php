<?php


namespace App\Models;
class Actions extends Model {
	
	public $tableName = 'actions';
	public $translated_fields = array('details','name');
	public $force_translation = false;
	public $destination_dir = 'images/actions/';
	
	public $hasOne = array(
			array("Model" => "Members","referenceKey" => "member_id","foreignKey" => "id")
			);
	public function __construct() {
		
		parent::__construct();
		
		$this->validations = array(
				
				// array('author_name' => array('required' => true, 'minlength' => 10)),
				// array('author_email' => array('required' => true,'email' => true)),
				// array('name' => array('required' => true, 'minlength' => 5)),
				array('details' => array('required' => true, 'minwords' => 2, 'maxwords' => 50,'maxlength' => 200)),
				// array('author_img' => array('image' => true, 'filesize' => 500000)),
				array('img' => array('image' => true, 'filesize' => 500000)),
				// array('url' => array('url' => true)),
				);
		
		
		
	}
	
	public function validate(){
		// print_r($_POST);
		// die('fuu');
		$until = $this->f3->get('POST.until');
		
		if(empty($until)){
			$this->f3->clear('POST.until');
		}
		
		
		$this->saveFile('img');
		
		// $this->saveFile('author_img');
		
		// return true;
		return parent::validate();
	}
	public function beforeSave(){
		
		if(!$this->validate())
		{
			return false;
		}
		
		if($this->f3->exists('SESSION.user')){
			$this->f3->set('POST.member_id',$this->f3->get('SESSION.user.id'));
		}
		
		// $details = $this->f3->get('POST.details');
		// $details = str_replace($details,"'","");
		// $this->f3->set('POST.details',$details);
		
		if(!$this->f3->exists('POST.original_article')){
			$when = $this->f3->get('POST.when'); //' '.$this->f3->get('POST.when_hour').':'.$this->f3->get('POST.when_min');
			
			$this->f3->set('POST.when',date('Y-m-d',strtotime($when)));
			
			
			$slug = $this->slugify($this->f3->get('POST.name'));
			$this->f3->set('POST.slug',$slug);
			$slug_duplicate = $this->getRow("WHERE slug like '".$slug."'");
			if(!empty($slug_duplicate)){
				$slug .= uniqid();
				$this->f3->set('POST.slug',$slug);
			}
			
		}
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
			if(isset($rows[$key]['id']) && isset($rows[$key]['name'])){
				$rows[$key]['share_url'] = '/expando/add/index.htm?u='.$this->f3->get('url').'/campaigns/view/'.$rows[$key]['id'].'&t='.urlencode($rows[$key]['name']);
			}
			if(isset($rows[$key]['details'])){
				$rows[$key]['summary'] = getSummary($rows[$key]['details'],200);
			}
			$rows[$key]['tempo_intervalo'] = fdate($rows[$key]['when']).(!empty($rows[$key]['until']) ? ' até '.fdate($rows[$key]['until']) : '');
		}
		
		return $rows;
	}
	/*function getRows($conds = '',$select = '*',$showQuery = false)
	{
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
		$langs = $this->db->getRows("i18n_translations","WHERE table_name = '".$this->tableName."' AND field_id = $id ","distinct language");
		
		foreach($langs as $key => $val)
		{
			$langs_rt[] = $val['language'];
		}
		
		return $langs_rt;
	}
	
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
		// print_r($translated_langs);
		
		
		// print_r($approvedtranslatedlangs);
		
		foreach($translated_langs as $key => $val)
		{
			if(!in_array( $val['original_language'],$approvedtranslatedlangs)){
				$pending_moderation_langs[]= $val;
			}
			
		}
		
		return $pending_moderation_langs;
	}
	
	public function getMap($actions = '',$marker = 'images/menu_actions_black.png')
	{
		if(!isset($actions['results'])){
			
			$rows = parent::getRows("WHERE published = 1 ORDER BY `when` ASC","name,gps_coords,img,id,`when`,details");
		}else{
			$rows = $actions['results'];
		}
		
		$events = array();
		// print_r($rows);
		// die('fuu');
		foreach($rows as $key => $row){
			// echo "> <	".$row['name'].' => '.$row['published'].'<br>';
			if($row['published'] < 1){
				// die( "FIMMMMMMMMM<br><br>");
				// unset($rows[$key]);
				// echo "unset: ".$row['name'];
				continue;
			}
			if(empty($row['gps_coords'])){
				// echo "unset_gps: ".$row['name'];
				continue;
			}
			$html = "";
			if(!empty($row['img'])){
				$html .= 
				"<a href='/en/actions/view/".$row['slug']."'><img class='thumbnail' src='/".$row['img']."'></a>
				";
			}
			
			$url = "/en/actions/view/".$row['slug'];
			$until = $row['until'] > 0 ? ' - '.date('F d, Y',strtotime($row['until'])) : '';
			$html .= "<h2>".date('F d, Y',strtotime($row['when'])).$until."</h2>";
			$html .= "<a href='".$url."'><h2>".$row['name']."</h2></a>";
			// $html .= "<h2>".$row['location']."</h2>";
			$html .= "<div class='summary_map'>".strip_tags($row['details'])."</div>";
			
			$html .= "<a href='$url'> > Read more</a>";
			// $html .= "<a href='".$url."'> > Read more</a>";
			$row['html'] = $html;
			$row['url'] = $url;
			$row['marker'] = $marker;
			
			$events[] = $row;
		}
		
		// print_r($rows);
		// echo "<pre>";
		// print_r($events);
		// die(1);
		return $events;
	}
}


