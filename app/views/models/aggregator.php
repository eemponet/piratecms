<?php


namespace App\Models;

include ('funcs.php');
include('app/libs/simplepie.php');

class Aggregator extends Model {
	
	public $tableName = 'aggregator';
	// public $translated_fields = array('summary');
	public $force_translation = false;
	// public $destination_dir = 'images/members/';
	
	// public $hasMany = array(array("Model" =>"Pedidostiposestados","referenceKey" => "tipo_estado_id"),
                            // array("Model" => "Utilizadores", "referenceKey" => "cliente_id", "foreignKey" => "id"),
                            // array("Model" => "Motoristas", "referenceKey" => "colaborador_id", "foreignKey" => "colaboradores_id")                                
                        // );
	public $hasOne = array(
			array("Model" => "Members","referenceKey" => "member_id","foreignKey" => "id"),
			array("Model" => "MembersSocial","referenceKey" => "member_social_id","foreignKey" => "id"));
	public function __construct() {
		
		parent::__construct();
		
	}
	
	public function getLastGUID($member_id,$type)
	{
		$row = $this->getRow("WHERE member_id = $member_id AND type like '$type' ORDER BY created DESC","guid");
		
		if(empty($row['guid']))
			return "";
		
		return $row['guid'];
		
	}
	public function addRSS($member_id,$rss_url){
		
		
		
		$feed = new \SimplePie();
		
		$feed->set_feed_url($rss_url);
		$feed->enable_cache(false);
		$feed->init();
		
		$items = $feed->get_items();
		foreach ($items as $item)
		{
			// var_dump($item);
			// die(1);
			// echo $item->get_title() . "<br>";
			$this->addRSSItemSimplepie($item,$member_id);
		}
		// var_dump($feed->get_item_quantity());
		return true;
		
		if(empty($rss_url)){
			return false;
		}
		$rss_dom = new \DOMDocument();
		$rss_dom->load($rss_url);
		
		simplexml_load_string($rss_url);
		
		
		// echo "<pre>";
		// print_r($rss_dom);
	
		// echo $member_id." ".$rss_url;
		
		// if($member_id == 31){
		// 	print_r($rss_dom);
		// }
		// 	echo "</pre>";
		
		foreach ($rss_dom->getElementsByTagName('item') as $node) {
			$node->getElementsByTagName('title')->item(0)->nodeValue;
		}
		// return;
		foreach ($rss_dom->getElementsByTagName('item') as $node) {
			
			$this->addRSSItem($node,$member_id);
			
		}
	}
	public function addRSSItem($node,$member_id)
	{
		// $guid = $this->getLastGUID($member_id,'rss');
		
		// echo $guid."<br>";
		
		echo $member_id." ";
		
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'description' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			'postDate' => date("Y-m-d H:i",strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)),
			'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			'member_id' => $member_id,
			'type' => 'rss'
			);
		
		$guid = $this->getRow("WHERE guid like '".$item['guid']."' AND member_id = $member_id AND type like 'rss'");
		
		// echo $guid['guid']."<br>";
		if(empty($guid)){
			$item = $this->formatfields($item);
			echo "INSERTED: ".$item['title']." ".$item['link']."<br>";
			$this->db->insert($item,$this->tableName);
			return true;
		}
		
		// }
		
		return false;
	}
	
	public function addRSSItemSimplepie($node,$member_id)
	{
		// $guid = $this->getLastGUID($member_id,'rss');
		
		// echo $guid."<br>";
		
		// echo $member_id." ";
		
		$item = array ( 
			'title' => $node->get_title(),
			'description' => $node->get_content(),
			'link' => $node->get_link(),
			'date' => $node->get_date(),
			'postDate' => $node->get_date("Y-m-d H:i"),
			'member_id' => $member_id,
			'type' => 'rss'
			);
		
		$link = $this->getRow("WHERE link like '".$item['link']."' AND member_id = $member_id AND type like 'rss'");
		
		// $image = getImgFromHTML($item['description']);
		$e = $item['description'];
		
		$imgtag = "";
		$find1 = strpos($e,"<img");
		if($find1 !== false){
			
			$find2 = strpos(substr($e,$find1),">");
			if($find2 !== false){
				$imgtag = substr($e,$find1,$find2);
				if(!empty($imgtag)){
					$regex = "/src=\"(.*?)\"/"; 
					preg_match($regex, $imgtag, $matches);
					if(!empty($matches[1]))
					{
						$item['featured_image'] = $matches[1];
					}
				}
			}
		}
		
		if(empty($link['link'])){
			$item = $this->formatfields($item);
			echo "INSERTED: ".$item['title']." ".$item['link']."<br>";
			// echo "<pre>";
			// print_r($item);
			// echo "</pre>";
			$this->db->insert($item,$this->tableName);
			return true;
		}
		
		// }
		
		return false;
	}
	
	function formatfields($item)
	{
		
		foreach($item as $key => $val){
			
			$val = str_replace("'", "", $val);
			$val = str_replace('"', "'", $val);
			
			if(in_array($key,array("title","description","link","guid","date","postDate","type","featured_image"))){
				// $val = superentities($val);
				// $val = strip_tags($val,'<br><b><i><img><p><iframe><a>');
				$val = strip_tags($val,'<i><b><iframe>');
				$val = strip_tags($val);
				if($key != "description"){
					$item[$key] = "\"$val\"";
				}else{
					$item[$key] = $val;
				}
			}else{
				$item[$key] = $val;
			}
			
			
		}
		
		if(strlen($item['description']) > 200){
			$i = 200;
			while(!empty($item['description'][$i]) && $item['description'][$i] != ' ' && $i < 300 ){
				$i++;
			}
			$item['description'] = substr($item['description'],0,$i);
			 $item['description'] .= "...";
		}
		
		$item["description"] = "\"".$item["description"]."\"";
		
		return $item;
	}
	public function addTwitter($member_id,$twitter_account,$twitter_hashtags){
		$twitter = new \App\Plugins\Twitter();
		
		if(!empty($twitter_account)){
			$data = $twitter->getTimeline($twitter_account);
			
			$this->addTwits($member_id,$data);
		}
		// if(!empty($twitter_account)){
		// 	$data = $twitter->getSearch($twitter_hashtags);
		// 	echo "<pre>";
		// 	print_r($data['statuses']);
		// 	$this->addTwits($member_id,$data['statuses']);
		// }
		
	}
	
	public function addTwits($member_id,$data)
	{
		
		foreach($data as $data_line)
		{
			// echo "FUUU ".$data_line['text']."<br>";
			
			$item = array ( 
				'title' => '',
				'description' => $data_line['text'],
				'link' => "https://twitter.com/".$data_line['user']['screen_name'].'/status/'.$data_line['id_str'],
				// https://twitter.com/HousingRightsLA/status/591037570412191745
				'date' => $data_line['created_at'],
				'postDate' => date("Y-m-d H:i",strtotime($data_line['created_at'] )),
				'guid' => $data_line['id_str'],
				'member_id' => $member_id,
				'type' => 'twitter'
				);
			
			
			$guid = $this->getRow("WHERE guid like '".$item['guid']."' AND member_id = $member_id AND type like 'twit'");
			
			if(empty($guid)){
				$item = $this->formatfields($item);
				echo "INSERTED: ".$item['title']." ".$item['link']."<br>";
				$this->db->insert($item,$this->tableName);
			}
		}
		
	}
	
	public function addFacebook($member_id,$facebook_account){
		
		$fb = new \App\Plugins\Facebook();
		
		$data = $fb->getWall($facebook_account);
		
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		// $url = "https://graph.facebook.com/stopdemolicoes";
		
		
	}
	
	public function getRss($page = 1,$member_id = '')
	{
		$member_sql = "";
		if(!empty($member_id)){
			$member_sql = " AND aggregator.member_id = ".$member_id;
		}
		return $this->paginate($page,2,'postDate','DESC',",members WHERE  members.id = aggregator.member_id AND members.published = 1 AND aggregator.visible = 1  AND type like 'rss' ".$member_sql,'aggregator.id','aggregator.*');
	}
	
	public function getSocial($page = 1,$member_id = '')
	{
		
		$member_sql = "";
		if(!empty($member_id)){
			$member_sql = " AND aggregator.member_id = ".$member_id;
		}
		return $this->paginate($page,4,'postDate','DESC',",members WHERE  members.id = aggregator.member_id AND members.published = 1 AND aggregator.visible = 1  AND type like 'twit' ".$member_sql,'aggregator.id','aggregator.*');
	}
}


