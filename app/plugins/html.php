<?php
namespace App\Plugins;

class html{
	static function guestlink($href,$content = '',$array_attrs = array() )
	{
		$user = \Base::instance()->get('SESSION.user');
		if(empty($user)){
			\App\Plugins\html::link($href,$content,$array_attrs);
		}
		
	}
	
	static function adminlink($href,$content = '',$array_attrs = array() )
	{
		$user = \Base::instance()->get('SESSION.user');
		if(!empty($user) && $user['is_admin']){
			\App\Plugins\html::link($href,$content,$array_attrs);
		}
	}
	
	static function userlink($href,$content = '',$array_attrs = array() )
	{
		$user = \Base::instance()->get('SESSION.user');
		if(!empty($user['id']) ){
			\App\Plugins\html::link($href,$content,$array_attrs);
		}
	}
	
	static function link($href,$content = '',$array_attrs = array() )
	{
		
		$attrs = '';
		
		foreach($array_attrs as $key => $val)
		{
			$attrs .= " $key='$val'";
		}
		
		$uri = \Base::instance()->get('BASE');
		$lang = \Base::instance()->get('lang_set');
		
		if(!empty($lang)){
			$lang_href = "/$lang/";
		}else{
			$lang_href = '';
		}
		
		
		if(strpos($href,"http://") !== false || strpos($href,"https://") !== false || strpos($href,"mailto:") !== false)
		{
			$uri = "";
			$attrs .= "target='_blank'";
			$lang_href = "";
		}
		if(strpos($href,"/") === 0)
		{
			$href = substr($href,1);
		}
		$content = i18n($content);
		
		echo "<a href='".$uri."$lang_href".$href."' $attrs>$content</a>";
	}
	static function getLink($href)
	{
		$url = \Base::instance()->get('url');
		$lang = \Base::instance()->get('lang_set');
		$uri = \Base::instance()->get('uri');
		
		
		if(!empty($lang)){
			$lang_href = "/$lang";
		}else{
			$lang_href = '';
		}
		
		echo $url."$lang_href".$uri."$href";
	}
	static function email($name,$array_attrs )
	{
		html::input($name,"email",$array_attrs);
	}
	static function text($name,$array_attrs = array())
	{
		html::input($name,"text",$array_attrs);
	}
	static function password($name,$array_attrs)
	{
		html::input($name,"password",$array_attrs);
	}
	static function hidden($name,$value = '')
	{
		if(empty($value) && \Base::instance()->exists('POST.'.$name))
		{
			$value = \Base::instance()->get('POST.'.$name);
		}
		echo "<input type='hidden' name='$name' value='$value'>";
	}
	static function file($name,$array_attrs = array())
	{
		$attrs = '';
		$label = '';
		$validation = '';
		if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
			if(!isset($array_attrs['class'])){
				$array_attrs['class'] = "";
			}
			$array_attrs['class'] .= " validation-error";
			
			$validation = "<label class='field-validation-error'>".\Base::instance()->get('SESSION.validate.fields.'.$name)."</label>";
		}
		
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val form-control'";
			}else if($key == "label"){
				$label = "<label>$val: </label>";
			}else{
				$attrs .= " $key='$val'";
			}
			
		}
		echo $label;
		
		echo "<input type='file' name='$name' $attrs>";
		echo $validation;
	}
	static function comboHour($name,$values,$array_attrs = array() )
	{
		$values = array();
		$hour = 1;
		while($hour <= 24){
			$values[$hour] = $hour;
			$hour++;
		}
		
		\App\Plugins\html::combo($name,$values,$array_attrs);
	}
	static function comboMinute($name,$values,$array_attrs = array() )
	{
		$values = array();
		$min = 0;
		while($min <= 60){
			$values[$min] = $min;
			$min++;
		}
		
		\App\Plugins\html::combo($name,$values,$array_attrs);
	}
	static function combo($name,$values,$array_attrs = array() )
	{
		$attrs = '';
		$label = '';
		$selected = '';
		$validation = "";
		
		if(empty($selected) && \Base::instance()->exists('POST.'.$name))
		{
			$selected = \Base::instance()->get('POST.'.$name);
		}
		
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val form-control'";
			}else if($key == "label"){
				$label = "<label>$val: </label>";
			}elseif($key == "selected"){
				$selected = $val;
			}else{
				$attrs .= " $key='$val'";
			}
			
		}
		
		if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
			if(!isset($array_attrs['class'])){
				$array_attrs['class'] = "";
			}
			$array_attrs['class'] .= " validation-error";
			
			$validation = "<br><label class='field-validation-error'>".\Base::instance()->get('SESSION.validate.fields.'.$name)."</label>";
		}
		
		echo $label;
		echo "<select name='$name' $attrs>";
		echo "<option></option>";
		foreach($values as $k => $val)
		{
			$sel = '';
			if($k == $selected)
			{
				$sel = 'selected';
			}
			echo "<option value='$k' $sel>$val</option>";
		}
		echo "</select>";
		echo "$validation";
		
	}
	
	static function input($name,$type,$array_attrs )
	{
		$attrs = '';
		$label = '';
		$validation = '';
		if($type == 'radio'){
			if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
				if(!isset($array_attrs['class'])){
					$array_attrs['class'] = "";
				}
				$array_attrs['class'] .= " validation-error";
				
				if(isset($array_attrs['validate_class'])){
					$clas = $array_attrs['validate_class'];
					$validation = "<script>$(document).ready(function(){
					$('$clas').addClass('validation-error');});</script>";
				}
			}
		}else{
			if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
				if(!isset($array_attrs['class'])){
					$array_attrs['class'] = "";
				}
				$array_attrs['class'] .= " validation-error";
				
				$validation = "<label class='field-validation-error'>".\Base::instance()->get('SESSION.validate.fields.'.$name)."</label>";
			}
		}
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val form-control'";
			}else if($key == "label"){
				$label = "<label>$val: </label>";
			}else{
				if(!empty($val)){
					$attrs .= " $key='$val'";
				}else{
					$attrs .= " $key ";
				}
			}
			
		}
		
		
		
		if($type == 'radio'){
			
			if(\Base::instance()->exists('POST.'.$name))
			{
				$value = \Base::instance()->get('POST.'.$name);
				if($array_attrs['value'] == $value){
					$attrs .= " checked ";
				}
			}
		}else{
			if(!isset($array_attrs['value']) && \Base::instance()->exists('POST.'.$name))
			{
				$attrs .= " value='".\Base::instance()->get('POST.'.$name)."' ";
			}
		}
		echo "$label<input type='$type' name='$name' $attrs>$validation";
	}
	static function textArea($name,$array_attrs = array() )
	{
		$label = '';
		$attrs = '';
		$value = '';
		$validation = '';
		if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
			if(!isset($array_attrs['class'])){
				$array_attrs['class'] = "";
			}
			$array_attrs['class'] .= " validation-error";
			
			$validation = "<label class='field-validation-error'>".\Base::instance()->get('SESSION.validate.fields.'.$name)."</label>";
		}
		if(!isset($array_attrs['value']) && \Base::instance()->exists('POST.'.$name))
		{
			$array_attrs['value'] = \Base::instance()->get('POST.'.$name);
		}
		
		if(empty($array_attrs["rows"]))
		{
			$array_attrs["rows"] = 3;
		}
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val form-control'";
			}else if($key == "label"){
				$label = "<label>$val: </label>";
			}else if($key == "value"){
				$value = $val;
			}else{
				$attrs .= " $key='$val'";
			}
		}
		echo "$label<textarea name='$name' $attrs>$value</textarea>$validation";
	
	}
	static function startForm($array_attrs = array()){
		$attrs = '';
		$validate = false;
		$id = '';
		
		foreach($array_attrs as $key => $val)
		{
			// if($key == "action")
			// {
			// 	$val = \App\Plugins\html::makeLink($val);
			// }
			
			if($key == "id"){
				$id = $val;
			}
			$attrs .= " $key='$val'";
		}
		
		echo "<form role='form' $attrs>";
	}
	static function endForm($array_attrs = array()){
		echo "</form>";
	}
	
	static function submit($text = "submit",$array_attrs = array())
	{
		$attrs = "";
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val'";
			}else{
				$attrs .= " $key='$val'";
			}
		}
		echo "<br/><br/><button type='submit' $attrs>$text</button>";
	}
	
	static function img($imgurl,$array_attrs = array()){
		$fw=\Base::instance();
		$uri = $fw->get('uri');
		$attrs = "src='$uri$imgurl'";
		foreach($array_attrs as $key => $val)
		{
			$attrs .= " $key='$val'";
		}
		
		echo "<img $attrs>";
	}
	
	static function pagination($total_pages, $page, $url)
	{
		echo "<div class='pagination'>";
		echo "<ul>";
		
		for ($i = 1; $i <= $total_pages; $i++){  
                        if($i == $page)
                        {
                                echo "<li class='active'>";
                        }else
                        {
                                echo "<li>";
                        }
                        
                        echo "<a href='".$url."$i'>$i</a>";
                        echo "</li>";
                }
                
                echo "</ul>";
                echo "</div>";
	}
	
	static function pagination2($total_pages, $page, $url,$max_pages = 10,$class = 'page')
	{
	    $inicio = 1;
	    $fim = $total_pages;
		//$max_pages = (int) \$max_pages / 2;
		echo "<ul class='pagination'>";
		$second_part = 0;
        if($total_pages > $max_pages){
            $inicio = $page - $max_pages;
            
            if($inicio < 1)
            {
                $max_pages = $inicio * -1 + $max_pages;
                $inicio = 1;
                
            }
            $fim = $page + $max_pages;
        }
        if($fim > $total_pages){
        	$fim = $total_pages;
        }
        //echo $total_pages." ".$max_pages." ".$inicio." - ".$fim;
        
        for ($i = $inicio; $i <= $fim; $i++){  
                        if($i == $page)
                        {
                                echo "<li class='active'>";
                        }else
                        {
                                echo "<li>";
                        }
                        
                        echo "<a class='$class' page='$i' href='".\App\Plugins\html::makeLink($url)."$i'>$i</a>";
                        echo "</li>";
                }
                
                echo "</ul>";
	}
	
	static function defaultValue($name){
		if(\Base::instance()->exists('POST.'.$name))
		{
			echo "value='".\Base::instance()->get('POST.'.$name)."'";
		}
		
	}
	
	static function checkbox($name,$caption,$array_attrs = array()){
		$attrs = '';
		$validation = '';
		if(\Base::instance()->exists('SESSION.validate.fields.'.$name)){
			if(!isset($array_attrs['class'])){
				$array_attrs['class'] = "";
			}
			$array_attrs['class'] .= " validation-error";
			
			$validation = "<span class='field-validation-error'>(".\Base::instance()->get('SESSION.validate.fields.'.$name).")</span>";
		}
		
		foreach($array_attrs as $key => $val)
		{
			if($key == "class"){
				$attrs .= " $key='$val btn btn-default'";
			}else{
				$attrs .= " $key='$val'";
			}
		}
		
		$checked = '';
		
		if(!isset($array_attrs['checked']) && \Base::instance()->exists('POST.'.$name)  )
		{ 
			$val = \Base::instance()->get('POST.'.$name);
			if($val){
				$checked = 'checked';
			}
		}
		
		echo "<input type='checkbox' name='$name' $attrs $checked>$caption $validation ";
	}
	
	private static function makeLink($link){
		$lang = \Base::instance()->get('lang_set');
		$lang_href = '';
		if(!empty($lang)){
			$lang_href = "/$lang";
		}
		
		$uri = substr(\Base::instance()->get('uri'),0,-1);
		
		return $uri.$lang_href.$link;
		
	}
	
	public static function genForm($form_attrs,$fields){
		
		// echo "<pre>";
		// print_r($fields);
		// echo "</pre>";
		\App\Plugins\html::startForm($form_attrs);
		foreach($fields as $field){
			$type = $field['type'];
			// $form .= $type."<br>";
			echo "<p>";
			switch($type){
				
				case "text":
					\App\Plugins\html::text($field['name'],array('placeholder' => $field['name'],'label' => $field['name']));
					break;
				case "hidden":
					\App\Plugins\html::hidden($field['name']);
					break;
				case "datepicker":
					\App\Plugins\html::text($field['name'],array('placeholder' => $field['name'],'label' => $field['name'],'class' => 'datepicker'));
					break;
				case "combobox":
					\App\Plugins\html::combo($field['name'],$field['data'],array('placeholder' => $field['name'],'label' => $field['name']));
					break;
				default:
					echo "Error: [$type] field type not found ";
			}
			echo "</p>";
			// $form .= "<br>";
			
		}
		// if(!$this->exists('POST.'))
		\App\Plugins\html::submit("Guardar");
		\App\Plugins\html::endForm();
		// return $form;
	}
	
	//http://www.the-art-of-web.com/php/truncate/
	public static function truncateHTML($input,$characters = 100){
		
		$input = substr($input,0,$characters);
		
		$opened = array();
		
		// loop through opened and closed tags in order
		if(preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches)) {
			foreach($matches[1] as $tag) {
				if(preg_match("/^[a-z]+$/i", $tag, $regs)) {
					// a tag has been opened
					if(strtolower($regs[0]) != 'br') $opened[] = $regs[0];
				} elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs)) {
					// a tag has been closed
					unset($opened[array_pop(array_keys($opened, $regs[1]))]);
				}
			}
		}
		
		// close tags that are still open
		if($opened) {
			$tagstoclose = array_reverse($opened);
			foreach($tagstoclose as $tag) $input .= "</$tag>";
		}
		
		echo $input;
	}
	
	static function i18n($id)
	{
		return i18n($id);
	}
	
	static function admineditable($url){
		
		$user = \Base::instance()->get('SESSION.user');
		if(!empty($user) && $user['is_admin']){
			echo "class='admineditable' url='$url'";
		}
	}
	
	static function translateditable($url){
		echo "class='translateditable' url='$url'";
	}
	
	static function hashtagify($input)
	{
		
		$hashtags = explode(" ",$input);
		
		foreach($hashtags as $hashtag){
			$hashtag = str_replace("#","",$hashtag);
			echo "<a href='https://twitter.com/hashtag/$hashtag' target='_blank'>#".$hashtag."</a> ";
		}
	}
	static function hashtagtwittify($input)
	{
		$hashtags = explode(" ",$input);
		$url_query = "https://twitter.com/search?q=";
		$c = 0;
		foreach($hashtags as $hashtag){
			$c++;
			$hashtag = str_replace("#","",$hashtag);
			$url_query .= "%23".$hashtag;
			if(count($hashtags) != $c){
				$url_query .= "+OR+";
			}
		}
		echo $url_query;
		// =%23php+OR+%23javascript+OR+%23html5
	}
	/*
	static function editable($page){
		$user = \Base::instance()->get('SESSION.user');
		
		if(!empty($user) && $user['is_admin']){
			// $admin_edit = "class='admineditable' url='$url'";
			$id = $page['id']; 
			echo "<div class='admineditable' url='/configs/edithtmlpage/$id'>";
		}
		// echo "<div $admin_edit>
		// {{@page.value_1 | raw}}";
		echo $page['value_1'];
        if(!empty($user) && $user['is_admin']){
        	echo "</div>";
        }
        
	}*/
}

// $methods = get_class_methods("\App\Plugins\html");
// foreach($methods as $method){
// 	// \Template::instance()->extend($method,'\App\Plugins\html::'.$method);
// 	\Template::instance()->extend("{{".$method."}}",'\App\Plugins\html::'.$method);
// }

// print_r($methods);
// die('UFU');
// \Template::instance()->extend('html','\App\Plugins\html::my_tag_renderer');


// \Template::instance()->extend('i18n',function($node){
		// var_dump($node);
	// echo \App\Plugins\html::i18n($node[0]);
// });t