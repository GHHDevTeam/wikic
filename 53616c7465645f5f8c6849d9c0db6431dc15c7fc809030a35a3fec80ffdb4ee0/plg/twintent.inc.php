<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: twintent.inc.php
//       ver 0.1 2017.Feb.6 H.Tomose
//

function plugin_twintent_convert()
{
	global $retdata;


	$retdata = <<<EOD
<a href='http://twitter.com/share' class='twitter-share-button'>Tweet</a><script type='text/javascript' src='http://platform.twitter.com/widgets.js'></script>
EOD;

	return $retdata;
}


?>
