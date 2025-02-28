<?php
// 【名    称】編集用ツールバーの表示するプラグイン
// 【作 成 者】Takuchan <http://lunatear.net/>
// 【Virsion 】0.2.0a
// 【作 成 日】2005/07/10
// 【動作 Ver】PukiWiki 1.4.5_1 でのみ動作確認
// 【License 】GPL v2 or (at your option) any later version
// 【備    考】attachref.inc.phpが入って居るとさらに幸せかもしれません
//
// PukiWiki Plus!さんのソース(lib/html.php)を元に
// Original Copyright.
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: html.php,v 1.36.14 2005/05/16 13:25:43 miko Exp $
// Copyright (C)
//   2005      Customized/Patched by Miko.Hoshina
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
/////////////////////////////////////////////////
//
//【使 い 方】///////////////////////////////////
//
///// 標準のPukiWikiに変更が必要な点 ////
// default.ini.php の顔文字の辺りに下記を追記//
//	'\s(\(T\-T)'    => ' <img alt="$1" src="' . IMAGE_DIR . 'face/tear.png" />',
//	'\s(\(\^Q\^)'   => ' <img alt="$1" src="' . IMAGE_DIR . 'face/huh.png" />',
//	'\s(\(\^_\-)'   => ' <img alt="$1" src="' . IMAGE_DIR . 'face/wink.png"/>',
//
///// 構成 /////
//
// このファイル以外は全てPukiWiki Plus! <http://pukiwiki.cafelounge.net/plus/>
// のパッケージより入手してください。
// (動作確認したのは pukiwiki-1.4.5plus-u2-eucjp.tar.gz のパッケージに含まれる
//  構成ファイルです)
//
// なお、配置場所は以下の通りです
//
// PukiWiki BaseDir/
//                  plugin/inputtoolbar.inc.php  (このファイル)
//                  skin/assistant.js
//                       assistloaded.js
//                       gecko.js
//                       other.js
//                       winie.js
//                       inputhelper.js (PukiWiki Plus!のdefault.jsを
//                                       この名前にリネームして使用)
//                  image/face/*  PukiWiki Plus!のファイルを全て使用
//                        plus/*  PukiWiki Plus!のファイルを全て使用
/////////////////////////////////////////////////

/////////////////////////////////////////////////

//表示部
function plugin_inputtoolbar_convert()
{
	global $script, $vars, $foot_explain, $head_tags;
	if(!(PKWK_ALLOW_JAVASCRIPT)){return '';}
	$head_tags[]    = '<script type="text/javascript" src="'. SKIN_DIR . 'inputhelper.js"></script>';
	$foot_explain[] = '<script type="text/javascript" src="'. SKIN_DIR . 'assistloaded.js" ></script>';
	$string         = plugin_inputtoolbar_form_assistant();
	return '<div id ="inputtoolbar" onmouseover="javascript:pukiwiki_pos();">' . $string . '</div>';
}

function plugin_inputtoolbar_form_assistant()
{
       global $html_transitional;
       static $assist_loaded = 0;      // for non-reentry

       $html_transitional = TRUE;
       if (!$assist_loaded) {
               $assist_loaded++;
               $map = <<<EOD

<map id="map_button" name="map_button">
<area shape="rect" coords="0,0,22,16" title="URL" alt="URL" href="#" onclick="javascript:pukiwiki_linkPrompt('url'); return false;" />
<area shape="rect" coords="24,0,40,16" title="B" alt="B" href="#" onclick="javascript:pukiwiki_tag('b'); return false;" />
<area shape="rect" coords="43,0,59,16" title="I" alt="I" href="#" onclick="javascript:pukiwiki_tag('i'); return false;" />
<area shape="rect" coords="62,0,79,16" title="U" alt="U" href="#" onclick="javascript:pukiwiki_tag('u'); return false;" />
<area shape="rect" coords="81,0,103,16" title="SIZE" alt="SIZE" href="#" onclick="javascript:pukiwiki_tag('size'); return false;" />
</map>
<map id="map_color" name="map_color">
<area shape="rect" coords="0,0,8,8" title="Black" alt="Black" href="#" onclick="javascript:pukiwiki_tag('Black'); return false;" />
<area shape="rect" coords="8,0,16,8" title="Maroon" alt="Maroon" href="#" onclick="javascript:pukiwiki_tag('Maroon'); return false;" />
<area shape="rect" coords="16,0,24,8" title="Green" alt="Green" href="#" onclick="javascript:pukiwiki_tag('Green'); return false;" />
<area shape="rect" coords="24,0,32,8" title="Olive" alt="Olive" href="#" onclick="javascript:pukiwiki_tag('Olive'); return false;" />
<area shape="rect" coords="32,0,40,8" title="Navy" alt="Navy" href="#" onclick="javascript:pukiwiki_tag('Navy'); return false;" />
<area shape="rect" coords="40,0,48,8" title="Purple" alt="Purple" href="#" onclick="javascript:pukiwiki_tag('Purple'); return false;" />
<area shape="rect" coords="48,0,55,8" title="Teal" alt="Teal" href="#" onclick="javascript:pukiwiki_tag('Teal'); return false;" />
<area shape="rect" coords="56,0,64,8" title="Gray" alt="Gray" href="#" onclick="javascript:pukiwiki_tag('Gray'); return false;" />
<area shape="rect" coords="0,8,8,16" title="Silver" alt="Silver" href="#" onclick="javascript:pukiwiki_tag('Silver'); return false;" />
<area shape="rect" coords="8,8,16,16" title="Red" alt="Red" href="#" onclick="javascript:pukiwiki_tag('Red'); return false;" />
<area shape="rect" coords="16,8,24,16" title="Lime" alt="Lime" href="#" onclick="javascript:pukiwiki_tag('Lime'); return false;" />
<area shape="rect" coords="24,8,32,16" title="Yellow" alt="Yellow" href="#" onclick="javascript:pukiwiki_tag('Yellow'); return false;" />
<area shape="rect" coords="32,8,40,16" title="Blue" alt="Blue" href="#" onclick="javascript:pukiwiki_tag('Blue'); return false;" />
<area shape="rect" coords="40,8,48,16" title="Fuchsia" alt="Fuchsia" href="#" onclick="javascript:pukiwiki_tag('Fuchsia'); return false;" />
<area shape="rect" coords="48,8,56,16" title="Aqua" alt="Aqua" href="#" onclick="javascript:pukiwiki_tag('Aqua'); return false;" />
<area shape="rect" coords="56,8,64,16" title="White" alt="White" href="#" onclick="javascript:pukiwiki_tag('White'); return false;" />
</map>
EOD;
       }
       $js = '<script type="text/javascript" src="'. SKIN_DIR . 'assistant.js" ></script>';
       return <<<EOD
$map
$js
EOD;
}
?>