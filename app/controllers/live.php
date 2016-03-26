<?php
namespace App\Controllers;

class Live extends ControllerApp{
	protected $models = array('Aggregator','Members');
	
	function index()
	{
		$this->f3->set('aggregations',$this->Aggregator->getRss());
		
		$this->f3->set('aggregations_social',$this->Aggregator->getSocial());
		// Aggregator->paginate(1,4,'postDate','DESC',',members WHERE m'.$this->sql_show.' AND type like "twit"','aggregator.id'));
	}
	
	function social()
	{
		$this->layout = 'blank';
		
		if($this->f3->exists('PARAMS.p2')){
			$this->f3->set('aggregations_social',$this->Aggregator->getSocial($this->f3->get('PARAMS.p1'),$this->f3->get('PARAMS.p2')));
			$this->f3->set('hidememberinfo',true);
		}else{
			$this->f3->set('aggregations_social',$this->Aggregator->getSocial($this->f3->get('PARAMS.p1')));
		}
	}
	
	function rss()
	{
		$this->layout = 'blank';
		if($this->f3->exists('PARAMS.p2')){
			$this->f3->set('aggregations',$this->Aggregator->getRss($this->f3->get('PARAMS.p1'),$this->f3->get('PARAMS.p2')));
			$this->f3->set('hidememberinfo',true);
		}else{
			$this->f3->set('aggregations',$this->Aggregator->getRss($this->f3->get('PARAMS.p1')));
		}
	}
	
	function delete()
	{
		
		$this->msg($this->getTranslation('submission_ok'));
		echo $this->f3->get('SESSION.user.id');
		if($this->Members->isAdmin()){
			$this->Aggregator->update(" id = ".$this->f3->get('PARAMS.p1'),array('visible' => "0"),true);
		}else{
			$this->Aggregator->update("member_id = ".$this->f3->get('SESSION.user.id')." AND id = ".$this->f3->get('PARAMS.p1'),array('visible' => "0"),true);
		}
		$this->goback();
	}
	
	
}