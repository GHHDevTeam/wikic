<?php
// topicpath plugin for PukiWiki
//   available under the GPL

//	defaultpageを一番最初に表示するかどうか。TRUE:表示する FALSE:表示しない.
define("PLUGIN_TOPICPATH_TOP_DISPLAY",FALSE);
//	$defaultpageに対するラベル
define("PLUGIN_TOPICPATH_TOP_LABEL","FrontPage");
//	階層を区切るセパレータ
define("PLUGIN_TOPICPATH_TOP_SEPARATOR"," / ");
//	自分のページに対するリンクを表示するかどうか
define("PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY",TRUE);

function plugin_topicpath_convert() {
	global $script,$vars;
	global $WikiName,$defaultpage;

	if ($vars["page"] == $defaultpage) return;

	$topic_path = "";
	if (PLUGIN_TOPICPATH_TOP_DISPLAY) $topic_path = make_link("[[".PLUGIN_TOPICPATH_TOP_LABEL.">".$defaultpage."]]");
	$div_page = preg_split("/\//",strip_bracket($vars["page"]),-1,PREG_SPLIT_NO_EMPTY);

	if (!PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY) array_pop($div_page);

	if( is_array($div_page) ){
		$stairway = array();
		foreach($div_page as $key => $element){
			$stairway[]		= $element;
			$landing		= join("/",$stairway);
			if (substr(S_VERSION, 0, 3) == "1.3") {
				if (preg_match("/^($WikiName)$/",$landing) == 0) $landing	= "[[" . $landing . "]]";
			}
			$element		= htmlspecialchars($element);
			if ($topic_path != "") 	$topic_path .= htmlspecialchars(PLUGIN_TOPICPATH_TOP_SEPARATOR);
			$topic_path		.= "<a href=\"$script?".rawurlencode($landing)."\">$element</a>";
		}
	}
	return $topic_path;
}

function plugin_topicpath_inline() {
	return plugin_topicpath_convert();
}
?>
