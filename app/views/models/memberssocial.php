<?php


namespace App\Models;
class MembersSocial extends Model {
	
	public $tableName = 'members_social';
	// public $translated_fields = array('summary');
	public $force_translation = false;
	// public $destination_dir = 'images/members/';
	
	public $types = array("rss" => "Rss", "facebook" => "Facebook", "twitter_hashtag" => "Twitter Hashtag", "twitter_account" => "Twitter account");
	
	public function __construct() {
		
		parent::__construct();
		
		$this->validations = array(
				/*
				array('name' => array('required' => true, 'minlength' => 10)),
				array('email' => array('required' => true,'email' => true)),
				array('website' => array('required' => true, 'minlength' => 10)),
				array('summary' => array('required' => true, 'minwords' => 100, 'maxwords' => 200)),
				array('text' => array('required' => true, 'minwords' => 200)),
				array('original_language' => array('required' => true)),
				array('author_img' => array('image' => true, 'filesize' => 500000)),
				array('img' => array('image' => true, 'filesize' => 500000))*/
				);
		
		
		
	}
	
	
	public function beforeSave(){
		
		if(!$this->validate())
		{
			return false;
		}
		
		// $this->saveFile('avatar');
		
		return true;
	}
	
	function getTypes()
	{
		
		return $this->types;
	}
	
	function getAllByType($type)
	{
		
		return $this->db->getRows("members_social,members","WHERE members_social.member_id = members.id AND members.published = 1 AND members_social.type like '$type'","members_social.*");
		
	}
	
}


