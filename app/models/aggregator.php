<?php


namespace App\Models;


// include('app/libs/simplepie.php');
include('app/libs/simplepie/autoloader.php');

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
		
		echo $rss_url;
		
		$items = $feed->get_items();
		// echo " (".count($items).") ";
		
		
		
		foreach ($items as $item)
		{
			$this->addRSSItemSimplepie($item,$member_id);
		}
		
		
		
		
		$this->db->update('members'," id = $member_id",array('aggregator_last_update' => "'".date("Y-m-d H:i:s")."'"));
		
		
		return count($items);
		
		
		
		
		
		// echo "<pre>";
		// echo "FUUUU";
		
		// $rss_url = "http://bondprecairewoonvormen.nl/feed/atom/";
		// die(1);
		// $homepage = file_get_contents($rss_url);
		// get contents of xyz.com, with connection timeout value of 1 seconds
		
		// echo $rss_url;
		
		// $html = curl_get_contents($rss_url, 30);
		
		// echo $html;
		
		// echo $homepage;
		
		// die('fuuukams');
		
		// var_dump($feed->get_item_quantity());
		// echo "</pre>";
		
		// $rss_dom = new \DOMDocument();
		// $rss_dom->load($rss_url);
		
		// simplexml_load_string($rss_url);
		
		
		// echo "<pre>";
		// print_r($rss_dom);
	
		// echo $member_id." ".$rss_url;
		
		// if($member_id == 31){
		// 	print_r($rss_dom);
		// }
		// 	echo "</pre>";
		
		// foreach ($rss_dom->getElementsByTagName('item') as $node) {
		// 	$node->getElementsByTagName('title')->item(0)->nodeValue;
		// }
		// // return;
		// foreach ($rss_dom->getElementsByTagName('item') as $node) {
			
		// 	$this->addRSSItem($node,$member_id);
			
		// }
		
		
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
		echo "<pre>";
		print_r($item);
		echo "</pre>";
		$link = $this->getRow("WHERE link like '".$item['link']."' AND type like 'rss'");
		
		// $image = getImgFromHTML($item['description']);
		$e = $item['description'];
		
		$imgtag = "";
		$find1 = strpos($e,"<img");
		if($find1 !== false){
			echo "..";
			$find2 = strpos(substr($e,$find1),">");
			if($find2 !== false){
				echo "..1";
				$imgtag = substr($e,$find1,$find2);
				if(!empty($imgtag)){
					
					echo "..2";
					$regex = "/src=\"(.*?)\"/"; 
					preg_match($regex, $imgtag, $matches);
					if(!empty($matches[1]))
					{
						echo "..3";
						$err = false;
						$info = pathinfo($matches[1]);
						if(!isset($info['extension'])){
							echo "error while downloading: ".$matches[1];
							$err = true;
						}
						
						
						if(!in_array($info['extension'],array('png','jpg','jpeg'))){
							echo "..4";
							echo $info['extension'];
							$err = true;
						}
						
						if(!$err){
							if(strpos($info['extension'],'jpg') == 0 && strpos($info['extension'],'jpg') !== false){
								$info['extension']  = 'jpg';
							}
							// $info['extension']  = 'jpg';
							$filename = 'images/articles/'.uniqid();
							$dst = $filename.'.'.$info['extension'];
							
							file_put_contents($dst, file_get_contents($matches[1]));
							
							fimage($dst,$filename,'jpg');
							
							$item['featured_image'] = '/'.$filename.'.jpg';
							// echo "downloading file... <img src='/".$filename.".jpg'> $filename .... <br>";
							echo "replacing: ".$matches[1]." to ".$item['featured_image']."...";
							$item['description'] = str_replace($matches[1],$item['featured_image'],$item['description']);
						}
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
			// return false;
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
	
	public function addTwitterAction($action_id,$twitter_account,$twitter_hashtags){
		
		
		$twitter = new \App\Plugins\Twitter();
		
		if(!empty($twitter_account)){
			$data = $twitter->getTimeline($twitter_account);
			
			$this->addTwits(array("action_id" => $action_id),$data);
		}
		if(!empty($twitter_hashtags)){
			
			$hashtags = explode(" ",$twitter_hashtags);
			
			foreach($hashtags as $hashtag){
				$data = $twitter->getSearch($hashtag);
				$this->addTwits(array("action_id" => $action_id),$data['statuses']);
			}
			
		}
	}
	public function addTwitter($member_id,$twitter_account,$twitter_hashtags){
		$twitter = new \App\Plugins\Twitter();
		
		if(!empty($twitter_account)){
			$data = $twitter->getTimeline($twitter_account);
			
			$this->addTwits(array('member_id' => $member_id),$data);
		}
		// if(!empty($twitter_account)){
		// 	$data = $twitter->getSearch($twitter_hashtags);
		// 	echo "<pre>";
		// 	print_r($data['statuses']);
		// 	$this->addTwits($member_id,$data['statuses']);
		// }
		
	}
	
	public function addTwits($ident,$data)
	{
		
		foreach($data as $data_line)
		{
			// echo "FUUU ".$data_line['text']."<br>";
			
			if(empty($data_line['text']))
			{
				continue;
			}
			if(isset($data_line['retweeted_status']) && is_array($data_line['retweeted_status'])){
				continue;
			}
			
			// else{
				// echo "<pre>";
				// print_r($data_line);
				// echo "</pre>";
				// die(1);
			// }
			
			// $all_data = json_encode($data_line);
			
			// $profile = array('profile_pic' => $data_line['user']['profile_image_url'],
							// 'profile_name' => $data_line['user']['screen_name'],
				// );
			// $all_data = str_replace($all_data,"'","\"");
			// print_r($all_data);
			$item = array ( 
				'title' => '',
				'description' => $data_line['text'],
				'link' => "https://twitter.com/".$data_line['user']['screen_name'].'/status/'.$data_line['id_str'],
				// https://twitter.com/HousingRightsLA/status/591037570412191745
				'date' => $data_line['created_at'],
				'postDate' => date("Y-m-d H:i",strtotime($data_line['created_at'] )),
				'guid' => $data_line['id_str'],
				'type' => 'twitter',
				'profile_pic' => '"'.$data_line['user']['profile_image_url'].'"',
				'profile_name' => '"'.$data_line['user']['screen_name'].'"',
				);
			
			$item = array_merge($item,$ident);
			
			// print_r($item);
			// print_r($ident);
			// die(1);
			$guid = $this->getRow("WHERE link like '".$item['link']."' AND type like 'twit'");
			
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
	
	public function getRss($page = 1,$member_id = '',$per_page = 6)
	{
		$member_sql = "";
		if(!empty($member_id)){
			$member_sql = " AND aggregator.member_id = ".$member_id;
		}
		return $this->paginate($page,$per_page,'postDate','DESC',"WHERE  aggregator.visible = 1  AND type like 'rss' ".$member_sql,'aggregator.id','aggregator.*',true);
	}
	
	
	public function getSocial($page = 1,$member_id = '', $per_page = 10)
	{
		$member_sql = "1";
		if(!empty($member_id)){
			$member_sql = " aggregator.member_id = ".$member_id;
		}
		
		// return $this->getTwits($page,"$member_sql AND  members.id = aggregator.member_id AND members.published = 1");
		
		return $this->paginate($page,$per_page,'postDate','DESC',",members WHERE aggregator.visible = 1  AND type like 'twit' AND ".$member_sql. " AND  members.id = aggregator.member_id AND members.published = 1 ",'aggregator.id','aggregator.*');
		
	}
	
	public function getTwits($page = 1,$sql = '')
	{
		// $member_sql = "";
		
		// if(!empty($member_id)){
			// $member_sql = " AND aggregator.member_id = ".$member_id;
		// }
		return $this->paginate($page,10,'postDate','DESC'," WHERE aggregator.visible = 1  AND type like 'twit' AND ".$sql,'aggregator.id','aggregator.*');
		
	}
	
	function getRows($conds = '',$select = '*',$showQuery = false)
	{
		$rows = parent::getRows($conds,$select,$showQuery);
		foreach($rows as $key => $valu){
			if(isset($rows[$key]['title'])){
				$rows[$key]['enctitle'] = urlencode($rows[$key]['title']);
			}
		}
		
		return $rows;
	}
}


