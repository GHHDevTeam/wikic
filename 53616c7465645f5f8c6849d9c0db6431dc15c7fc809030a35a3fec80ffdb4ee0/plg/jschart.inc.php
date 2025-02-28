<?php
// PukiWiki - Yet another WikiWikiWeb clone
// JSChart plugin
//
// JSChart : http://www.jschart.jp/
//
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2007      Konuma <konuma@ark-web.jp> (Original author)
//
// License: GNU/GPL
//
// Thanks to http://www.jschart.jp/
//

define("TYPE_VARIATION", "1,2,3");

function plugin_jschart_convert()
{

	if (func_num_args() > 5) {
		$args   = func_get_args();
                $title = urlencode(array_shift($args));
                $type =  array_shift($args);
                $width = array_shift($args);
                $height = array_shift($args);
                $values = implode(",", $args);

                if (!in_array($type, explode(",", TYPE_VARIATION) ) ) {
                        die_message("グラフのタイプには[" . TYPE_VARIATION . "]のどれかを設定してください。");
                }

                return <<<EOD
<script type="text/javascript" charset="utf-8" src="http://www.jschart.jp/t/?gt=$type&gd[$title]=$values&w=$width&h=$height"></script>
EOD;

	}else{
                die_message("jschartへの引数は5つ以上指定してください。");
        }

}
?>
