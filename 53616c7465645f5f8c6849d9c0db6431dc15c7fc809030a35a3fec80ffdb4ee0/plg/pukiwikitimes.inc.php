<?php
/*
 Copyright 2005 Hiroaki Kawai <kawai@apache.org>
 
 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*/
// $Id: pwtimes.inc.php 28 2005-02-21 17:09:20Z hawk $

// RecentChangesのキャッシュ
define('PLUGIN_PWTIMES_CACHE', CACHE_DIR . 'recent.dat');
define('PLUGIN_PWTIMES_TITLE',"p u k i w i k i  t i m e s");
define('PLUGIN_PWTIMES_WIDTH',300);
define('PLUGIN_PWTIMES_HEIGHT',40);

// Query String に使う KEY
define('PLUGIN_PWTIMES_WIDTH_KEY','w');
define('PLUGIN_PWTIMES_HEIGHT_KEY','h');
define('PLUGIN_PWTIMES_FONT_KEY','f');

function plugin_pukiwikitimes_convert(){
	$title=PLUGIN_PWTIMES_TITLE;
	$width=PLUGIN_PWTIMES_WIDTH;
	$height=PLUGIN_PWTIMES_HEIGHT;
	if(extension_loaded('gd')){
		return "<img src=\"$script?plugin=pukiwikitimes\" "
			. "alt=\"$title\" title=\"$title\" height=\"$height\" width=\"$width\" />";
	}else{
		return "gd extension not loaded.";
	}
}

function plugin_pukiwikitimes_action(){
	global $vars;
	
	$title=PLUGIN_PWTIMES_TITLE;
	$width=PLUGIN_PWTIMES_WIDTH;
	$height=PLUGIN_PWTIMES_HEIGHT;
	$font = "../53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/font/MS_Gothic.ttf"; 
	$fontsize=10;
	$hpad=8; // 水平方向余白
	$vpad=3; // 垂直方向余白
	$gap=2; // bar と font の間
	$recent_lines = 100; // たぶんこれ以上になると役に立たない？
	// query params 反映
	if(isset($vars[PLUGIN_PWTIMES_WIDTH_KEY])) $width=$vars[PLUGIN_PWTIMES_WIDTH_KEY]+0;
	if(isset($vars[PLUGIN_PWTIMES_HEIGHT_KEY])) $height=$vars[PLUGIN_PWTIMES_HEIGHT_KEY]+0;
	if(isset($vars[PLUGIN_PWTIMES_FONT_KEY])) $fontsize=$vars[PLUGIN_PWTIMES_FONT_KEY]+0;
	$im = imagecreatetruecolor($width, $height);
	$face = ImageColorAllocate ($im, 224, 80, 0); // foreground color
	$back = ImageColorAllocate ($im, 255, 255, 255); // background color
	
	ImageAntialias($im,true);
	ImageFilledRectangle($im,0,0,$width,$height,$back);
	
	// RECENT CACHE がない場合
	if (! file_exists(PLUGIN_PWTIMES_CACHE)){
		Header ("Content-type: image/png");
		ImageTTFText($im, $fontsize, 0, $hpad, ($fontsize+$height)/2, $face, $font, 'NO RECENT CACHE');
		ImagePNG($im);
		ImageDestroy ($im);
		exit;
	}
	
	$data=array();
	// 先頭 $recent_lines 件
	$lines = array_splice(file(PLUGIN_PWTIMES_CACHE), 0, $recent_lines);
	$date='';
	foreach ($lines as $line) {
		list($time, $page) = explode("\t", rtrim($line));
		if($date != $time) { // touch * 対策
			$tm=localtime(ZONETIME+$time,1);
			array_push($data,$tm['tm_hour']+$tm['tm_min']/60.0+$tm['tm_sec']/3600.0);
			$date=$time;
		}
	}
	
	if($height <= ($vpad+$fontsize+$gap)*2){
		ImageTTFText($im,$fontsize,0,$hpad,$height-$vpad,$face,$font,'too small');
	}else{
		// title
		$text=$title;
		$xpos=$hpad;
		ImageTTFText($im,$fontsize,0,$xpos,$vpad+$fontsize,$face,$font,$text);
	
		// bar background
		ImageFilledRectangle($im,$hpad,$vpad+$fontsize+$gap,$width-$hpad,$height-$vpad-$fontsize-$gap,$face);
	
		$tick=($width-$hpad*2)/6.0;
		$ypos=$height-$vpad;
	
		$text='0';
		$xpos=$hpad;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='4';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='8';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='12';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='16';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='20';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$text='24';
		$xpos=$xpos+$tick;
		$bb=ImageTTFBbox($fontsize,0,$font,$text);
		ImageTTFText($im,$fontsize,0,$xpos-($bb[2]-$bb[0])/2.0,$ypos,$face,$font,$text);
	
		$barlen=$width-$hpad*2.0;
		foreach($data as $dat){
			ImageLine($im,$hpad+$barlen*$dat/24.0,$vpad+$fontsize+$gap,
				$hpad+$barlen*$dat/24.0,$height-$vpad-$fontsize-$gap+1,$back);
		}
	}
	Header ("Content-type: image/png");
	ImagePNG($im);
	ImageDestroy ($im);
}

?>
