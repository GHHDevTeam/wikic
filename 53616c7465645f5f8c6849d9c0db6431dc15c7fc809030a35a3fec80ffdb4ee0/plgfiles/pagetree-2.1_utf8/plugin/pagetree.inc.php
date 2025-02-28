<?php

/**
 * pagetree.inc.php - Display treemenu without JavaScript
 *
 * @author     revulo
 * @licence    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html  GPLv2
 * @version    2.1
 * @link       http://www.revulo.com/PukiWiki/Plugin/PageTree.html
 */

// Move FrontPage to the top of the tree
if (!defined('PLUGIN_PAGETREE_TOP_DEFAULTPAGE')) {
    define('PLUGIN_PAGETREE_TOP_DEFAULTPAGE', true);
}

// Hide top-level leaf pages such as Help, MenuBar etc.
if (!defined('PLUGIN_PAGETREE_HIDE_TOPLEVEL_LEAVES')) {
    define('PLUGIN_PAGETREE_HIDE_TOPLEVEL_LEAVES', true);
}

// Ignore list
if (!defined('PLUGIN_PAGETREE_NON_LIST')) {
    define('PLUGIN_PAGETREE_NON_LIST', '');
}

// Include list
if (!defined('PLUGIN_PAGETREE_INCLUDE_LIST')) {
    define('PLUGIN_PAGETREE_INCLUDE_LIST', '');
}

// Markers
if (!defined('PLUGIN_PAGETREE_MARKER_COLLAPSED')) {
//  define('PLUGIN_PAGETREE_MARKER_COLLAPSED', '＋');
    define('PLUGIN_PAGETREE_MARKER_COLLAPSED',
        '<img src="' . IMAGE_DIR . 'pagetree_collapsed.gif" alt="＋" title="" />');
}
if (!defined('PLUGIN_PAGETREE_MARKER_EXPANDED')) {
//  define('PLUGIN_PAGETREE_MARKER_EXPANDED', '－');
    define('PLUGIN_PAGETREE_MARKER_EXPANDED',
        '<img src="' . IMAGE_DIR . 'pagetree_expanded.gif" alt="－" title="" />');
}
if (!defined('PLUGIN_PAGETREE_MARKER_LEAF')) {
//  define('PLUGIN_PAGETREE_MARKER_LEAF', '・');
    define('PLUGIN_PAGETREE_MARKER_LEAF',
        '<img src="' . IMAGE_DIR . 'pagetree_leaf.gif" alt="・" title="" />');
}


function plugin_pagetree_init()
{
    $messages['_pagetree_messages'] = array(
        'title'   => 'PageTree',
        'toppage' => 'Top'
    );
    set_plugin_messages($messages);
}

function plugin_pagetree_convert()
{
    global $vars;

    if (func_num_args()) {
        $current = func_get_arg(0);
    } else {
        $current = $vars['page'];
    }

    $pages = plugin_pagetree_get_pages($current);
    plugin_pagetree_filter_pages($pages);
    plugin_pagetree_sort_pages($pages);
    return plugin_pagetree_get_html($pages, $current);
}

function plugin_pagetree_get_pages($current)
{
    $tokens = explode('/', $current);
    $depth  = count($tokens);
    $ancestors[0] = '';
    for ($i = 0; $i < $depth; $i++) {
        $ancestors[$i + 1] = $ancestors[$i] . $tokens[$i] . '/';
    }

    $allpages =& plugin_pagetree_get_allpages();

    $pages = array();
    foreach ($allpages as $page) {
        $count = substr_count($page, '/');
        if ($count === 0 || ($count <= $depth && strpos($page, $ancestors[$count]) === 0)) {
            $pages[] = $page;
        }
    }
    return $pages;
}

function plugin_pagetree_filter_pages(&$pages)
{
    global $non_list;

    if (PLUGIN_PAGETREE_INCLUDE_LIST !== '') {
        $includes = preg_grep('/' . PLUGIN_PAGETREE_INCLUDE_LIST . '/', $pages);
    } else {
        $includes = array();
    }

    if (PLUGIN_PAGETREE_HIDE_TOPLEVEL_LEAVES) {
        $leaf =& plugin_pagetree_get_leaf_flags();
        foreach ($pages as $key => $page) {
            if (strpos($page, '/') === false && $leaf[$page] === true) {
                unset($pages[$key]);
            }
        }
    }

    if (PLUGIN_PAGETREE_NON_LIST !== '') {
        $pattern = '/(' . $non_list . ')|(' . PLUGIN_PAGETREE_NON_LIST . ')/';
    } else {
        $pattern = '/' . $non_list . '/';
    }
    if (version_compare(PHP_VERSION, '4.2.0', '>=')) {
        $pages = preg_grep($pattern, $pages, PREG_GREP_INVERT);
    } else {
        $pages = array_diff($pages, preg_grep($pattern, $pages));
    }

    if ($includes) {
        $pages += $includes;
    }
}

function plugin_pagetree_sort_pages(&$pages)
{
    $pages = str_replace('/', "\0", $pages);
    sort($pages, SORT_STRING);
    $pages = str_replace("\0", '/', $pages);
}

function plugin_pagetree_get_html($pages, $current)
{
    global $defaultpage, $_pagetree_messages;

    $script = get_script_uri();
    $script = './' . substr($script, strrpos($script, '/') + 1);

    $marker_collapsed = '<span class="collapsed">' . PLUGIN_PAGETREE_MARKER_COLLAPSED . '</span>';
    $marker_expanded  = '<span class="expanded">'  . PLUGIN_PAGETREE_MARKER_EXPANDED  . '</span>';
    $marker_leaf      = '<span class="leaf">'      . PLUGIN_PAGETREE_MARKER_LEAF      . '</span>';

    $html = '<h5>' . htmlspecialchars($_pagetree_messages['title']) . '</h5>' . "\n"
          . '<div class="pagetree">' . "\n";

    if (PLUGIN_PAGETREE_TOP_DEFAULTPAGE && $_pagetree_messages['toppage']) {
        $s_label = htmlspecialchars($_pagetree_messages['toppage']);
        if ($defaultpage === $current) {
            $html .= $marker_expanded
                  .  '<span class="current">' . $s_label . '</span>' . "\n";
        } else {
            $url   = $script;
            $title = htmlspecialchars($defaultpage);
            $html .= '<a href="' . $url . '" title="' . $title . '">'
                  .  $marker_expanded . $s_label . '</a>' . "\n";
        }
    }
    if ($pages) {
        $html .= '<ul>' . "\n";
    }

    $leaf =& plugin_pagetree_get_leaf_flags();
    $level = 1;
    foreach ($pages as $i => $page) {
        if ($level === 1) {
            $label = $page;
        } else {
            $label = substr($page, strrpos($page, '/') + 1);
        }
        $indents = str_repeat(' ', $level);
        $s_label = htmlspecialchars($label);

        $next_level = substr_count($pages[$i + 1], '/') + 1;

        if ($page === $current) {
            if ($leaf[$page] === true) {
                $html .= $indents . '<li class="leaf">' . $marker_leaf
                      .  '<span class="current">' . $s_label . '</span>';
            } else {
                $html .= $indents . '<li class="expanded">' . $marker_expanded
                      .  '<span class="current">' . $s_label . '</span>';
            }
        } else {
            $url   = $script . '?' . rawurlencode($page);
            $title = htmlspecialchars($page);

            if ($leaf[$page] === true) {
                $html .= $indents . '<li class="leaf">'
                      .  '<a href="' . $url . '" title="' . $title . '">'
                      .  $marker_leaf . $s_label . '</a>';
            } else if ($next_level > $level) {
                $html .= $indents . '<li class="expanded">'
                      .  '<a href="' . $url . '" title="' . $title . '">'
                      .  $marker_expanded . $s_label . '</a>';
            } else {
                $html .= $indents . '<li class="collapsed">'
                      .  '<a href="' . $url . '" title="' . $title . '">'
                      .  $marker_collapsed . $s_label . '</a>';
            }
        }

        if ($next_level === $level) {
            $html .= '</li>' . "\n";
        } else if ($next_level > $level) {
            $html .= '<ul>' . str_repeat('<li><ul>', $next_level - $level - 1) . "\n";
        } else {
            $html .= '</li>' . str_repeat('</ul></li>', $level - $next_level) . "\n";
        }
        $level = $next_level;
    }

    if ($pages) {
        $html .= '</ul>' . "\n";
    }
    $html .= '</div>' . "\n";

    return $html;
}

function &plugin_pagetree_get_allpages()
{
    static $pages = null;
    global $defaultpage;

    if ($pages === null) {
        $pages = get_existpages();
        if (PLUGIN_PAGETREE_TOP_DEFAULTPAGE) {
            $key = encode($defaultpage) . '.txt';
            unset($pages[$key]);
        }
    }
    return $pages;
}

function &plugin_pagetree_get_leaf_flags()
{
    static $leaf = array();

    if ($leaf === array()) {
        $pages =& plugin_pagetree_get_allpages();
        foreach ($pages as $page) {
            if (isset($leaf[$page])) {
                continue;
            }
            $leaf[$page] = true;

            while (($pos = strrpos($page, '/')) !== false) {
                $page  = substr($page, 0, $pos);
                $isset = isset($leaf[$page]);
                $leaf[$page] = false;
                if ($isset === true) {
                    break;
                }
            }
        }
    }
    return $leaf;
}

?>
