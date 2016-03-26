<?php
namespace App\Controllers;

// $ wget http://beta.housingnotprofit.org/en/scripts/twitter -O /tmp/twitter_tmp && cat /tmp/twitter_tmp > /var/nginx/logs/housing.log && rm /tmp/twitter_tmp

// wget http://beta.housingnotprofit.org/en/scripts/rss -O /tmp/rss_tmp && cat /tmp/rss_tmp > /var/nginx/logs/housing_rss.log && rm /tmp/rss_tmp


class Scripts extends ControllerApp{
	
	protected $models = array("Members","Aggregator","Events");
	public $twitter;
	
	function __construct() {
		parent::__construct();
		
		$this->layout = 'blank';
	}
	
	function index()
	{
		
	}
	
	function test()
	{
		echo "ola.... ";
		$url = "http://bondprecairewoonvormen.nl/feed/";
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$data = curl_exec($ch);
		
		// Check if any error occurred
		if(curl_errno($ch))
		{
			echo 'Curl error: ' . curl_error($ch);
		}

		curl_close($ch);
		
		echo "adeus.... ";
		return $data;
	}
	function rss(){
		echo date("Y-m-d H:i:s").":: A iniciar actualiza&ccedil;&atilde;o de rss...\n\r";
		$members = $this->Members->getRows("WHERE published = 1 ORDER BY aggregator_last_update ASC");
		
		foreach($members as $member){
			if(empty($member['rss_url'])){
				continue;
			}
			$count_added = $this->Aggregator->addRSS($member['id'],$member['rss_url']);
			echo "( $count_added) ";
			if($count_added < 1){
				continue;
			}
			break;
		}
		
		echo date("Y-m-d H:i:s").":: A finalizar actualiza&ccedil;&atilde;o de rss...\n\r";
		/*
		echo "<pre>";
		print_r($members);
		echo "</pre>";
		foreach($members as $member){
			
			$lastGUID = $this->Aggregator->getLastGUID($member['id'],'rss');
			
			if(empty($member['rss_url'])){
				continue;
			}
			$rss_dom = new \DOMDocument();
			$rss_dom->load($member['rss_url']);
			
			foreach ($rss_dom->getElementsByTagName('item') as $node) {
				
				$newGUID = $node->getElementsByTagName('guid')->item(0)->nodeValue;
				if($lastGUID == $newGUID)
				{
					break;
				}
				$this->Aggregator->addRSS($node,$member['id']);
				
			}
			
		}
		*/
	}
	
	function twitter(){
		
		$actions = $this->Events->getRows("WHERE (twitter_account is not null OR twitter_hashtags is not null ) AND published = 1 AND (`when` > now() OR until > now() ) ORDER BY last_aggregator_update ASC LIMIT 5");
		foreach($actions as $action){
			echo " A actualizar evento ".$action['name']."...<br>";
			$this->Aggregator->addTwitterAction($action['id'],$action['twitter_account'],$action['twitter_hashtags']);
			$this->Events->update("id = ".$action['id'],array('last_aggregator_update' => 'now()'));
		}
		
		$members = $this->Members->getRows("WHERE published = 1 ORDER BY last_aggregator_update ASC LIMIT 5");
		foreach($members as $member){
			echo " A actualizar membro ".$member['name']."...<br>";
			$this->Aggregator->addTwitter($member['id'],$member['twitter_account'],$member['twitter_hashtags']);
			$this->Members->update("id = ".$member['id'],array('last_aggregator_update' => 'now()'));
		}
		
		
	}
	
	function facebook(){
		$members = $this->Members->getRows("WHERE published = 1");
		
		
		foreach($members as $member){
			$this->Aggregator->addFacebook($member['id'],$member['facebook_account']);
		}
		
	}
	
}