<?php
/**
* @file
* @brief Microsoft Age of Mythologyプレイヤーシグネチャ画像表示プラグイン
*
* PukiWiki Plugin http://pukiwiki.org/
*
* @note
* 引数\n
* - name	: プレイヤー名(省略不可)
* - theme	: テーマ(省略=0)
* - site	: サイト(省略=0)
* - gametype: ゲームタイプ(省略=Supremacy)
*	- Supremacy
*	- Conquest
*	- Lightning
*	- Deathmatch
* 
* @code
*	&aomsig(name, theme, site, gametype);
* @endcode
*
* @author DEX(http://dex.qp.land.to/)
* @date 2005-07-12	v1.0	新規作成
* 
* $Id:
*/

/// Microsoft Age of Mythologyプレイヤーシグネチャ画像表示 インラインプラグイン
function plugin_aomsig_inline()
{
	// 画像表示サイト
	$aImg = array('http://www.nervenexus.com/eso/signature_stats.php?Name=%s&MapType=%s&theme=%s',
				'http://sig.die-ohne-clan.de/sig.php?name=%s&gametype=%s',
				);
	$aImgSize = array(
				"300x50",
				"280x50",
				);
	// リンク先
	$aLink = array(
		'http://www.nervenexus.com/eso/eso_stats.php?ESOName=%s&gametype=%s',
		);

	// 引数
	$argc = func_num_args();
	$argv = func_get_args();
	$argv = array_map("trim", $argv);
	$argv = array_map("urlencode", $argv);
	list($name, $theme, $site, $gametype) = $argv;

	if(is_null($name) || empty($name)) return '';
	if(is_null($theme) || empty($theme)) $theme = 0;
	if(is_null($site) || empty($site)) $site = 0;
	if(is_null($gametype) || empty($gametype)) $gametype = 'Supremacy';

	$img = sprintf($aImg[$site], $name, $gametype, $theme);
	$link = sprintf($aLink[0], $name, $gametype, $theme);
	list($imageX, $imageY) = split("x", $aImgSize[$site]);
	
	$result =<<<EOD
<a href="{$link}">
	<img src="{$img}" width="{$imageX}" height="{$imageY}" border="0" alt="{$name}" />
</a>
EOD;
	return $result;
}

?>
