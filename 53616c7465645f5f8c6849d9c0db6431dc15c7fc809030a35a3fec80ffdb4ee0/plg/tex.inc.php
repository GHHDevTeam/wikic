<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: tex.inc.php,v 1.06 2011/02/03 15:15 abicky Exp $
// Copyright (C)
//   2010-2011 abicky
// License: GPL v2 or (at your option) any later version
// Based on ref.inc.php
//   2002-2009 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
//
// LaTeX Math Expression plugin
//
// ライセンスの書き方がよくわからない…

/************** Default settings **************/
// Google Chart AIP URI
define('API_HOST', 'chart.apis.google.com');
define('API_URI', 'http://' . API_HOST . '/chart?cht=tx&chl=');
// Horizontal alignment
define('PLUGIN_TEX_DEFAULT_ALIGN', 'left'); // 'left', 'center', 'right'
// Default text color
define('PLUGIN_TEX_DEFAULT_COLOR', '#000000');
// Default background color
define('PLUGIN_TEX_DEFAULT_BGCOLOR', '#ffffff00');
// Directory where images are saved
define('IMG_DIR', './teximg/');
/**********************************************/

// Usage (a part of)
define('PLUGIN_TEX_USAGE', '($tex mathmatical expression$ [,options]) see also <a href=\'http://abicky.site90.com/pukiwiki/index.php?Plugins%2Ftex.inc.php\'>here</a>');

function plugin_tex_inline()
{
    $args = func_get_args();
    array_pop($args);
    $params = plugin_tex_body($args);

    if (isset($params['_error']) && $params['_error'] != '') {
        // Error
        return '&amp;tex(): ' . $params['_error'] . ';';
    } else {
        return $params['_body'];
    }
}

function plugin_tex_convert()
{
    $params = plugin_tex_body(func_get_args());

    if (isset($params['_error']) && $params['_error'] != '') {
        return "<p>#tex(): {$params['_error']}</p>\n";
    }

    if ($params['around']) {
        if($params['_align'] == 'right') $style = 'float:right';
        else if($params['_align'] == 'left') $style = 'float:left';
        else $style = "text-align:{$params['_align']}";
    } else {
        $style = "text-align:{$params['_align']}";
    }

    // divで包む
    return "<div class=\"img_margin\" style=\"$style\">{$params['_body']}</div>\n";
}

function plugin_tex_body($args)
{
    global $use_proxy;

    if($args[0] == '') {
        $params[_error] = 'Usage: ' . PLUGIN_TEX_USAGE;
        return $params;
    }

    // 戻り値
    $params = array(
        'left'   => FALSE, // 左寄せ
        'center' => FALSE, // 中央寄せ
        'right'  => FALSE, // 右寄せ
        'around' => FALSE, // 回り込み
        'link' => FALSE,   // 元ファイルへのリンクを張る
        'url' => FALSE,    // Google Chart API のURLを表示する
        'noimg'  => FALSE, // 画像を展開しない
        'zoom'   => FALSE, // 縦横比を保持する
        '_size'  => FALSE, // サイズ指定あり
        '_w'     => 0,     // 幅
        '_h'     => 0,     // 高さ
        '_%'     => 0,     // 拡大率
        '_color' => substr(PLUGIN_TEX_DEFAULT_COLOR, 1),     // 数式の色
        '_bg'    => substr(PLUGIN_TEX_DEFAULT_BGCOLOR, 1),     // 背景色
        '_args'  => array(),
        '_done'  => FALSE,
        '_error' => ''
        );

    // 数式
    $eq = '';
    $has_dollar = substr($args[0], 0, 1) == '$';
    //$has_dollar = preg_match('/^\$(.*)\$(.*)/', join($args, ','), $matches);

    if ($has_dollar) {
        // 第一引数: "数式"を取得
        preg_match('/^\$(.*)\$,?(.*)/', join($args, ','), $matches);
        $eq = $matches[1];
        $args = explode(',', str_replace(' ', '', $matches[2]));
    }
    if (!$eq) {
        $eq = join($args, ',');
        $args = '';
    }

    $title = htmlspecialchars($eq);
    // In Google Chart "\mathbf xy" means "\mathbf{xy}".
    // mathbb -> mathb, mathit -> mat, mathrm -> mathr, mathfrak -> mathf, mathcal -> mathc
    $fontType = array('bm', 'mathbb?', 'mathc(?:a|al)?', 'mathbf',
                      'mat(?:h|hi|hit)?', 'mathrm?', 'mathf(?:r|ra|rak)?','mb(?:o|ox)?',
                      'ope(?:r|ra|rat|rato|rator|ratorn|ratorna|ratornam|ratorname)?');
    $pattern = '/\\\\('.join('|', $fontType).')\s*?(\\\\[a-zA-Z\$]+|(?<=\s)[^\s\{\}])/';
    $eq = preg_replace($pattern, '\\\\$1{$2}', $eq);
    // convert unexpected '$' to '\$'
    $eq = preg_replace('/(?<!\\\\)\$/', '\\\$', $eq);
    // Google Chart doesn't support "\bm".
    $eq = str_replace('\\bm', '\\mathbf', $eq);
    $pre = $eq;
    // convert equations like '\frac{a}{\alpha}' to '\fr a \alpha'
    $eq = preg_replace('/\\\\fr(?:a|ac)?\s*{(.|\\\\[a-zA-Z]+)}\s*{(.|\\\\[a-zA-Z]+)}/', '\fr $1 $2', $eq);
    // remove extra spaces
    // a b -> ab, \bm{a} b -> \bm{a}b, \frac{a}{b} c -> \frac{a}{b}c,
    // \alpha a -> \alpha a, a\ b -> a\ b, \alpha \beta -> \alpha\beta
    // one space after a backslash shouldn't be removed
    //$eq = str_replace('\\ ', "\\\n", $eq);
    $eq = preg_replace('/(?<!\\\\)((?:\\\\\\\\)*)\\\\ /', "$1\\\n", $eq);
    $eq = preg_replace(array('/\\\\mb(?:o|ox)?\s*{(.*?)}/e', '/(\\\\[a-zA-Z]+)\s+([a-zA-Z])/'), array("'\\mb{'.str_replace(' ', \"\n\", '$1').'}'", "$1\n$2"), $eq);
    $eq = str_replace(array(' ', "\n"), array('', ' '), $eq);
    
    // 残りの引数の処理
    if (! empty($args))
        foreach ($args as $arg)
            tex_check_arg($arg, $params);

    /*
      $eqをもとに以下の変数を設定
      $url : URL
      $title :タイトル
      $is_image : 画像のときTRUE
      $info : getimagesize()の'size'
    */
    $width = $height = 0;
    $matches = array();

    $is_image = (! $params['noimg'] );

    // 拡張パラメータをチェック
    if (! empty($params['_args'])) {
        $_title = array();
        foreach ($params['_args'] as $arg) {
            if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches)) {
                $params['_size'] = TRUE;
                $params['_w'] = $matches[1];
                $params['_h'] = $matches[2];

            } else if (preg_match('/^([0-9.]+)%$/', $arg, $matches) && $matches[1] > 0) {
                $params['_%'] = $matches[1];

            } else if (preg_match('/^#([0-9abcdef]{6})$/', $arg, $matches) && $matches[1] != '') {
                $params['_color'] = $matches[1];

            } else if (preg_match('/^bg#([0-9abcdef]{6,8})$/', $arg, $matches) && $matches[1] != '') {
                $params['_bg'] = $matches[1];

            } else {
                $_title[] = $arg;
            }
        }

        if (! empty($_title)) {
            $title = htmlspecialchars(join(',', $_title));
            if ($is_image) $title = make_line_rules($title);
        }
    }

    $eq_hash = md5($eq);

    $imgfile = IMG_DIR.$eq_hash.'.png';
    $msg = get_image($imgfile, $eq);

    if ($msg) {
        $params[_error] = $msg;
        return $params;
    }

    // 画像サイズ調整
    // 指定されたサイズを使用する
    if ($params['_size'] || $params['_%']) {
        $size = @getimagesize($imgfile);
        if (is_array($size)) {
            $width  = $size[0];
            $height = $size[1];
            $info   = $size[3];
        }

        if ($params['_size']) {
            if ($width == 0 && $height == 0) {
                $width  = $params['_w'];
                $height = $params['_h'];
            } else if ($params['zoom']) {
                $_w = $params['_w'] ? $width  / $params['_w'] : 0;
                $_h = $params['_h'] ? $height / $params['_h'] : 0;
                $zoom = max($_w, $_h);
                if ($zoom) {
                    $width  = (int)($width  / $zoom);
                    $height = (int)($height / $zoom);
                }
            } else {
                $width  = $params['_w'] ? $params['_w'] : $width;
                $height = $params['_h'] ? $params['_h'] : $height;
            }
        } else {
            $width  = (int)($width  * $params['_%'] / 100);
            $height = (int)($height * $params['_%'] / 100);
        }
        if ($width && $height){
            $info = 'width="'.$width.'" height="'.$height.'" ';
            $_size = '&chs='.$width.'x'.$height;
        }
    }

    $_args = $_size.'&chco='.$params['_color'].'&chf=bg,s,'.$params['_bg'];
    $args_hash = md5($_args);
    $imgfile = IMG_DIR.$eq_hash.$args_hash.'.png';
    $msg = get_image($imgfile, $eq, $_args);
    if ($msg) {
        $params[_error] = $msg;
        return $params;
    }

    // アラインメント判定
    $params['_align'] = PLUGIN_TEX_DEFAULT_ALIGN;
    foreach (array('right', 'left', 'center') as $align) {
        if ($params[$align])  {
            $params['_align'] = $align;
            break;
        }
    }

    if($is_image){
        $params['_body'] = "<img src='$imgfile' alt='$title' title='$title' $info/>";
        if ($params['link'])
            $params['_body'] = "<a href='$imgfile' title='$title'>{$params['_body']}</a>";
    } else {
        $params['_body'] = "<a href='$imgfile' title='$info'>$title</a>";
    }
    if($params['url'])
        $params['_body'] .= "<br>\n<a href='$url'>Generated by Google Chart</a>";

    // for debug
    //$params['_body'] .= "<br>$title<br>$pre<br>$eq";

    return $params;
}

// オプションを解析する
function tex_check_arg($val, & $params)
{
    if ($val == '') {
        $params['_done'] = TRUE;
        return;
    }
    if (! $params['_done']) {
        foreach (array_keys($params) as $key) {
            if (strpos($key, strtolower($val)) === 0) {
                $params[$key] = TRUE;
                return;
            }
        }
        $params['_done'] = TRUE;
    }

    $params['_args'][] = $val;
}

function get_image($imgfile, $eq, $_args = '')
{
    global $use_proxy, $no_proxy, $proxy_host, $proxy_port;
    global $need_proxy_auth, $proxy_auth_user, $proxy_auth_pass;

    if (!file_exists($imgfile)) {
        if (strlen($eq) > 200) {
            $eq = shorten($eq);
        }
        if (strlen($eq) > 200) {
            return 'Error! Exceeds the maximum formula length of 200 characters.';
        }
        $url = API_URI . urlencode($eq) . $_args;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($use_proxy && ! in_the_net($no_proxy, API_HOST)) {
                // Cannot get the image if CURLOPT_HTTPPROXYTUNNEL is TRUE
                //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                curl_setopt($ch, CURLOPT_PROXY, $proxy_host . ':' . $proxy_port);

                // Basic-auth for HTTP proxy server
                if ($need_proxy_auth && isset($proxy_auth_user) && isset($proxy_auth_pass))
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth_user . ':' . $proxy_auth_pass);
            }
            $teximg = curl_exec($ch);
            if (curl_error($ch))
                return 'Error! cURL: ' . curl_error($ch);
            curl_close($ch);
        }else if ((bool)ini_get('allow_url_fopen')) {
            $rc = http_request($url);
            if (! $rc['header'])
                return 'Error! http_request: ' . $rc['data'];
            $teximg = $rc['data'];
        } else {
            return 'Error! Cannot get the image. Make sure that allow_url_fopen is \'On\'.';
        }
        if (substr($teximg, 1, 3) == 'PNG'){
            $f = fopen($imgfile, 'w');
            fwrite($f, $teximg);
            fclose($f);
        } else {
            if ($_args) {
                return 'Error! The image size is too large.';
            } else {
                return 'Error! The expression contains invalid characters.';
            }
        }
    }
    return 0;
}

function shorten($eq)
{
    $origin = array('hat','tilde','bar','ddot','dot','approx','simeq','equiv','mbox','neq','propto','ntriangleleft','ntrianglelefteq','ntriangleright','ntrianglerighteq','oplus','bigoplus','times','otimes','bigotimes','cdot','circ','bullet','star','frac','sin','cos','tan','sec','csc','arcsin','arccos','arctan','operatorname','lim','limsup','liminf','min','max','sup','exp','lg','log','ker','deg','gcd','hom','arg','dim','pmod','nabla','partial','forall','exists','empty','emptyset','subset','subseteq','supset','supseteq','bigcap','cup','bigcup','biguplus','setminus','sqsubset','sqsubseteq','sqsupset','sqsupseteq','sqcap','sqcup','bigsqcup','wedge','bigwedge','bigvee','sqrt','Diamond','triangle','perp','leftarrow','rightarrow','leftrightarrow','longleftarrow','longrightarrow','mapsto','nearrow','searrow','swarrow','nwarrow','uparrow','downarrow','updownarrow','rightharpoonup','rightharpoondown','leftharpoonup','leftharpoondown','Leftarrow','Rightarrow','Leftrightarrow','Longleftarrow','Longrightarrow','Longleftrightarrow','Uparrow','Downarrow','Updownarrow','longleftrightarrow','dagger','ddagger','smile','frown','triangleleft','triangleright','infty','bot','vdash','imath','ell','Re','Im','wp','clubsuit','spadesuit','flat','natural','sharp','boxdot','curlywedge','curlyvee','jmath','surd','ast','uplus','diamond','bigtriangleup','bigtriangledown','ominus','oslash','odot','bigcirc','amalg','prec','succ','preceq','succeq','dashv','asymp','parallel','stackrel','prime','overline','underline','vec','widehat','oint','Gamma','Delta','Theta','Lambda','Xi','Sigma','Upsilon','Phi','Psi','alpha','beta','gamma','delta','epsilon','zeta','eta','theta','iota','kappa','lambda','mu','nu','xi','pi','rho','sigma','upsilon','phi','chi','psi','omega','varepsilon','vartheta','varpi','varrho','varsigma','varphi','mathbb','mathit','mathrm','mathfrak','mathcal','aleph','quad','lbrace','rbrace','qquad','mbox','hspace','begin');
    $short = array('h','til','ba','dd','do','ap','sime','eq','mb','ne','prop','nt','nt','ntriangler','ntriangler','op','bigop','tim','ot','bigot','cd','ci','bu','st','fr','si','co','ta','se','cs','arcs','arc','arct','ope','li','lims','limin','mi','ma','su','e','l','lo','k','de','gc','ho','ar','di','pmo','na','pa','fo','exi','em','em','sub','subsete','sups','supsete','bigca','cu','bigc','bigu','set','sqs','sqs','sqsup','sqsup','sqca','sqc','bigsq','we','bigw','bigv','sq','Di','tri','pe','lefta','ri','leftr','longl','longr','map','nea','sea','sw','nw','upa','dow','upd','righth','rightharpoond','lefth','leftharpoond','Le','Ri','Leftr','Longl','Longr','Longleftr','Upa','Do','Upd','longleftr','da','dda','smi','fro','trianglel','triangler','inft','bo','vd','imat','el','R','I','w','cl','sp','fla','nat','sh','boxd','curlyw','cur','j','sur','as','up','dia','bigt','bigtriangled','omi','os','od','bigci','am','pr','suc','prece','succe','das','asy','para','stac','pri','overl','un','v','wideh','oi','G','De','T','Lam','X','Si','U','Ph','Ps','al','be','ga','del','ep','z','et','th','io','ka','lam','m','n','x','p','rh','sig','ups','ph','ch','ps','om','vare','vart','va','varr','vars','varph','mathb','mat','mathr','mathf','mathc','ale','qu','lbr','rbr','qq','mb','hs','beg');
    $pattern = array_map('make_pattern', $origin);
    $replace = array_map('make_replace', $short);

    return preg_replace($pattern, $replace, $eq);
}

function make_pattern($str) {
    return '/\\\\' . $str . '(?![a-zA-Z])/';
}
function make_replace($str) {
    return '\\' . $str;
}
?>





