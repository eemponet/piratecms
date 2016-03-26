<?php
namespace App\Controllers;

class Resources extends ControllerApp{
	
	protected $models = array('Configs');
	function index()
	{
		$this->f3->set('other_tools',$this->Configs->getPage('other_tools'));
	}
	function videos()
	{
		$this->f3->set('page',$this->Configs->getPage('videos'));
		
	}
	function leaflets()
	{
		$this->f3->set('page',$this->Configs->getPage('leaflets'));
		
	}
}
?>