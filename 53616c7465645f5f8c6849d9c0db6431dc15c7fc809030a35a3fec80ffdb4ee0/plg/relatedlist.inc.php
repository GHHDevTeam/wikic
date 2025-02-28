<?php
 
//relatedlist.inc.php
//based on related.inc.php
//Version 0.0.1
//作成者:どぼん！
 
//除外するページの正規表現パターン
define('PLUGIN_RELATEDLIST_EXCEPT', '/(?:^|\/)template$/');
 
function plugin_relatedlist_init()
{
    global $whatsnew, $whatsdeleted, $menubar;
    
    //除外するページを指定する
    $_plugin_relatedlist_messages = array(
        '_plugin_relatedlist_except_pages'=>
            array($whatsnew, $whatsdeleted, $menubar),
    );
    set_plugin_messages($_plugin_relatedlist_messages);
}
 
function plugin_relatedlist_convert()
{
    global $vars, $script, $non_list, $defaultpage;
    global $_plugin_relatedlist_except_pages;
 
    $args = func_get_args();
 
    $page = isset($args[0]) ?
        strip_bracket(array_shift($args)) : $vars['page'];
    if (empty($page)) $page = $defaultpage;
 
    $pages = array_keys(links_get_related_db($page));
    //$non_listを除外する
    $pages = array_diff($pages,
        preg_grep('/' . $non_list . '/S', $pages));
    //設定によりさらにページを除外する
    if (PLUGIN_RELATEDLIST_EXCEPT != '')
        $pages = array_diff($pages,
            preg_grep(PLUGIN_RELATEDLIST_EXCEPT, $pages));
    if (!empty($_plugin_relatedlist_except_pages))
        $pages = array_diff($pages, $_plugin_relatedlist_except_pages);
 
    if (empty($pages))
        return '<ul><li>No related pages found.</li></ul>' . "\n";
    
    return page_list($pages);
}
 
?>