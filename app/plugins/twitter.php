<?php 
namespace App\Plugins;

require_once('app/libs/TwitterAPIExchange.php');

class Twitter
{
	public $settings = array(
			'oauth_access_token' => "2785380209-Dk7E244zyWxx2xpQIkeTPbpOvKalwEoHcnfnAeQ",
			'oauth_access_token_secret' => "JukomatbBc4JtEQo7P2rsX4cwTYVRZdRkfscynaUErmls",
			'consumer_key' => "p7zHGFmEuyhgKKq7U1iapLnBG",
			'consumer_secret' => "5oEWuKuM6WbnhZiBeW2grLNzzMixbN8AdomtrISxiMLEOHxdJ8"
			);
	private $twitter;
	// function __construct()
	// {
		// $twitter = new \TwitterAPIExchange($this->settings);
	// }
	function getSearch($query)
	{
		
		return $this->queryGET('https://api.twitter.com/1.1/search/tweets.json',"?q=".$query."&count=100");
	}
	
	function getTimeline($twitteraccount)
	{
		return $this->queryGET('https://api.twitter.com/1.1/statuses/user_timeline.json',"?screen_name=".$twitteraccount."&count=100");
		
	}
	
	function queryGET($url,$query)
	{
		echo "trying:: ".$url.$query."<br>";
		// die('uuu');
		$twitter = new \TwitterAPIExchange($this->settings);
		// $url = 'https://api.twitter.com/1.1/search/tweets.json';
		// https://twitter.com/StopDemolicoes/status/600330946492178432
		// https://twitter.com/StopDemolicoes/status/591584528654819328
		$requestMethod = 'GET';
		try{
			$json = $twitter->setGetfield($query)->buildOauth($url, $requestMethod)->performRequest();
		}catch(Exception $e)
		{
			print_r($e);
			die('a');
		}
		
		//print_r($json);
		$data = json_decode($json,1);
		return $data;
	}
}