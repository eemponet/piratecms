<?php
// session_start();
if(!isset($_SESSION['lang'])){
	$_SESSION['lang'] = 'en';
}
$lang = $_SESSION['lang'];

include_once("app/langs/en.php");
if(file_exists("app/langs/$lang.php")){
	include_once("app/langs/$lang.php");
}
function superentities( $str ){
    $str2 = htmlentities($str);
    
    $str2 = str_replace("&gt;",">",$str2);
    $str2 = str_replace("&lt;","<",$str2);
    return $str2;
}
function getImgFromHTML($html) {
	if (stripos($html, '<img') !== false) {
		$imgsrc_regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
		preg_match($imgsrc_regex, $html, $matches);
		// unset($imgsrc_regex);
		// unset($html);
		if (is_array($matches) && !empty($matches)) {
			return $matches[2];
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function genstring($tamanho = 10, $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#!)(=&'){
		
		return substr(str_shuffle($alfabeto), 0, $tamanho);
	}


function i18n($id)
{
	global $langdefs;
	
	$lang = $_SESSION['lang'];
	
	if(isset($langdefs[$lang][$id])){
		return $langdefs[$lang][$id];
	}
	
	return $id;
}                      

function getSummary($html,$size){
	$rawtext = strip_tags($html);
	$rawtext = $html;
	$rawtext = substrhtml($html,0,$size);
	if(strlen($rawtext) > $size){
		$rawtext = substr($rawtext,0,$size);
		$rawtext .= "...";
	}
	
	return $rawtext;
}

function fdate($date,$format = '%B %e, %Y'){ // %A,, %H:%M
	
	return utf8_encode(strftime($format,strtotime($date)));
}


function curl_get_contents($url, $timeout = 5) {
    // Initiate the curl session
    $ch = curl_init();
    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);
    // follow any "Location: " header that the server sends
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    // Return the output instead of displaying it directly
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // The number of seconds to wait while trying to connect
    // Use 0 to wait indefinitely. 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    // Execute the curl session
    $data = curl_exec($ch);
    // Close the curl session
    curl_close($ch);
    return $data;
}
function substrhtml($str,$start,$len){
    $str_clean = substr(strip_tags($str),$start,$len);
    if(preg_match_all('/<[^>]+>/',$str,$matches,PREG_OFFSET_CAPTURE)){
        for($i=0;$i<count($matches[0]);$i++){
            $str_clean = substr($str_clean,0,$matches[0][$i][1]) . $matches[0][$i][0] . substr($str_clean,$matches[0][$i][1]);
        }
        return $str_clean;
    }else{
        return substr($str,$start,$len);
    }
}

function fimage($fsrc,$fdst,$fext, $w=300, $h=300, $crop=FALSE)
{
	
	
	list($width, $height) = getimagesize($fsrc);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    
    $path = pathinfo($fsrc);
    $info =  mime_content_type($fsrc);
    // if(strpos($info['extension'],'png') == 0 && strpos($info['extension'],'png') !== false){
    if(strpos($info,'image/png') == 0 && strpos($info,'image/png') !== false ){
    	$src = @imagecreatefrompng($fsrc);
    }else if((strpos($info,'image/jpg') == 0 && strpos($info,'image/jpg') !== false) || (strpos($info,'image/jpeg') == 0 && strpos($info,'image/jpeg') !== false)){
    	 $src = @imagecreatefromjpeg($fsrc);
    }else{
    	die($info);
    	// 
    }
    if(!$src){
    	return false;
    }
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    // imagepng($dst,$fdst.'.png');
    if($fext == 'png'){
    	imagepng($dst,$fdst.$fext);
    }else{
    	imagejpeg($dst,$fdst.$fext);
    }
}

?>