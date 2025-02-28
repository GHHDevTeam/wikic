<?php
// define() など。
function plugin_v2chdat_init()
{
    // 初期化関数。プラグインが呼び出されるとまず最初に実行されます。
  // 成功 (true) か失敗 (false) を返す、もしくは何も返しません。
  // 注：一度のセッション（PukiWiki実行)で一度だけ実行されるのが仕様です。
  define(PLUGIN_2CHDAT_LOG, CACHE_DIR.'2chdatviewer.log');
    define(PLUGIN_2CHDAT_CACHE, CACHE_DIR.'2chdat_');
//file_put_contents(PLUGIN_2CHDAT_LOG, "#Debug Log#\n");
}
function plugin_v2chdat_convert()
{
    // ブロック型。行頭で #2chdatviewer() と呼び出します。文字列を返します。
  $_args = func_get_args();
    $param = array('host', 'bbs', 'key', 'opt');
    foreach ($_args as $i => $_arg) {
        if (stristr($_arg, '=')) {
            list($argkey, $argvar) = explode('=', $_arg);
            $param[$argkey] = $argvar;
        } else {
            $param[$param[$i]] = $_arg;
        }
    }
    if (stristr($param[host], '.2ch.net')) {
        $param[host] = str_replace('.2ch.net', '.2ch.sc', $param[host]);
    }
    $_dat = dat2array(get2chdat($param[host], $param[bbs], $param[key]));
    $resnum = count($_dat);
    $resstart = $param[last] ? $param[last] : 5;
    $_body .= '<h3 id="content_1_0"><a href="'."http://$param[host]/test/read.cgi/$param[bbs]/$param[key]/".'">'.$_dat[1][title].'</a></h3><br />'."\n";
    if (stristr($param[host], '.2ch.')) {
        $_body .= <<<_HTML_
<p style="text-align:right;font-size:xx-small;"> 引用元：　http://$param[host]/test/read.cgi/$param[bbs]/$param[key]/ </p>
_HTML_;
    } else {
        $_body .= <<<_HTML_
<form method="GET" action="http://$param[host]/test/respost.cgi" accept-charset='Shift_JIS' onclick="document.charset='Shift_JIS';">
<input type="hidden" name="ref" value="$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" />
<input type="hidden" name="bbs" value="$param[bbs]" />
<input type="hidden" name="key" value="$param[key]" />
<input type="submit" value="書き込む" />
名前：<input type="text" name="name" style="width:30%;" />
E-mail<font size="1">（省略可）</font>：<input type="text" name="mail" style="width:25%;"><br>
<textarea name="msg" rows="5" style="width:95%;height:0%;"></textarea>
</form>
_HTML_;
    }
    $_body .= <<<_HTML_
<table class="thread" border=0 style="table-layout:fixed;width:100%;border:none;border-collapse: collapse;border-width:0;height:1px;position:relative;top:-.4em;border-bottom: 1px solid #ccc;">
  <colgroup>
    <col style="width:30%;" />
    <col style="width:70%;word-wrap:break-word;" />
  </colgroup>
  <thead id="head" style="border-bottom: 1px solid #ccc;">
    <th class="res_info_head" style="width:;">名前</th>
    <th class="res_detele_head" style="width:;">レス</th>
  </thead>
  <tbody>
_HTML_;
    for ($i = $resnum; $i > $resnum - $resstart; --$i) {
        if ($i <= 0) {
            break;
        }
        $_imglink = '';
        if (preg_match_all('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+\.(jpe?g|png|gifv?))', $_dat[$i][text], $result) !== false) {
            foreach ($result[0] as $value) {
                //URL表示
            $_imglink .= '<a href="'.$value.'" target="_break"><img src="'.$value.'" height=12% width=12% alt="'.$value.'" /></a>';
            }
        }
        $_body .= <<<_HTML_
  <tr id="r{$i}" class="res_page">
    <td class="res_info" style="vertical-align:top;">
        {$_dat[$i][num]} : <br />
        <div style="text-align:center;color:#1144aa;"><a href="mailto:{$_dat[$i][mail]}">{$_dat[$i][name]}</a><d/iv>
    </td>
    <td class="res_text" style="width:70%;word-wrap:break-word;">
      {$_dat[$i][text]}
      <br clear="all" />
      <span>
        {$_imglink}
      </span>
    </td>
  </tr>
  <tr style="border-bottom: 1px solid #ccc;">
    <td class="res_id" style="text-align:right;vertical-align:bottom">
      <span style="font-size:x-small;">
        {$_dat[$i][id]}
      </span>
    </td>
    <td class="res_date" style="text-align:right;vertical-align:bottom">
      <span style="font-size:x-small;">
        {$_dat[$i][date]}
      </span>
    </td>
  </tr>
_HTML_;
    }
    $_body .= '</tbody></table>'."\n";
    $_body .= '<p style="text-align:center;font-size:small;"> <a href="'."http://{$_SERVER[HTTP_HOST]}/?cmd=v2chdat&host={$param[host]}&bbs={$param[bbs]}&key={$param[key]}".'">すべて表示</a> </p>'."\n";
    $_body = str_replace(array("\r\n", "\r", "\n"), '', $_body);
    $_body = preg_replace('/>\s+</', '><', $_body);
    return $_body;
}
function plugin_v2chdat_inline()
{
    // インライン型。行内で &2chdatviewer(){}; と呼び出します。文字列を返します。
  global $vars;
    $_args = func_get_args();
    $param = array('host', 'bbs', 'key', 'title');
    foreach ($_args as $i => $_arg) {
        if (stristr($_arg, '=')) {
            list($argkey, $argvar) = explode('=', $_arg);
            $param[$argkey] = $argvar;
        } else {
            $param[$param[$i]] = $_arg;
        }
    }
  //$param = $vars;
  $_body = '';
    if (stristr($param[host], '.2ch.net')) {
        $param[host] = str_replace('.2ch.net', '.2ch.sc', $param[host]);
    }
    if ($param[host] && $param[bbs] && $param[key]) {
        $datcache = PLUGIN_2CHDAT_CACHE."{$param[host]}_{$param[bbs]}_{$param[key]}".'.dat';
    } else {
        $_body = '<p>変数が正しくありません。</p>'.$_body;
        $_body .= ' host='.htmlsc($param[host]);
        $_body .= ' bbs='.htmlsc($param[bbs]);
        $_body .= ' key='.htmlsc($param[key]);
        $_body .= '<br />';
        return $_body;
    }
/*
  if ($param[cache] == 'delete') {
    unlink ( $datcache );
    $_dat = get2chdat($param[host],$param[bbs],$param[key]);
  } else if (is_readable($datcache)) { 
    $_dat = file_get_contents($datcache);
  } else {
    $_dat = get2chdat($param[host],$param[bbs],$param[key]);
  }
*/
    if ($fp = fopen($datcache, 'r')) {
        $_datline = explode('<>', mb_convert_encoding(fgets($fp), 'utf8', 'sjis-win'));//UTFに変換
    }
    $_dattitle = ($_datline[4]) ? ($_datline[4]) : 'スレを表示する';
    $_title = ($param[title]) ? htmlsc($param[title]) : htmlsc($_dattitle);
    $_body .= '<p style="font-size:small;"> <a href="'."http://{$_SERVER[HTTP_HOST]}/?cmd=v2chdat&host={$param[host]}&bbs={$param[bbs]}&key={$param[key]}".'">'.$_title.'</a> </p>'."\n";
    $_body = str_replace(array("\r\n", "\r", "\n"), '', $_body);
    $_body = preg_replace('/>\s+</', '><', $_body);
    return $_body;
}
function plugin_v2chdat_action()
{
    // アクション型。index.php?cmd=2chdatviewer でアクセスしたときに呼び出されます
  // array('msg'=>'タイトル文字列','body'=>'本文'); を返します。本文が空文字の場合には read プラグインに移行します。
  global $vars;
    $param = $vars;
    $_body = '';
    if (stristr($param[host], '.2ch.net')) {
        $param[host] = str_replace('.2ch.net', '.2ch.sc', $param[host]);
    }
    if ($param[host] && $param[bbs] && $param[key]) {
        $datcache = PLUGIN_2CHDAT_CACHE."{$param[host]}_{$param[bbs]}_{$param[key]}".'.dat';
    } else {
        $_body = '<p>変数が正しくありません。</p>'.$_body;
        $_body .= ' host='.htmlsc($param[host]);
        $_body .= ' bbs='.htmlsc($param[bbs]);
        $_body .= ' key='.htmlsc($param[key]);
        $_body .= '<br />';
        return array('msg' => 'エラー', 'body' => $_body);
    }
    if ($param[cache] == $param[key]) {
        $_dat = get2chdat($param[host], $param[bbs], $param[key]);
    } elseif ($param[cache] == 'delete') {
        unlink($datcache);
        $_dat = get2chdat($param[host], $param[bbs], $param[key]);
    } elseif (is_readable($datcache)) {
        $_dat = file_get_contents($datcache);
    } else {
        $_body = '<p>キャッシュが在りません。未知のスレです。</p>';
        $_body .= ' cache='.htmlsc("{$param[host]}/{$param[bbs]}/{$param[key]}").'.dat';
        return array('msg' => 'エラー', 'body' => $_body);
    }
    $_dat = dat2array($_dat, stristr($param[host], '.2ch.sc'));
    $_body .= '<h3 id="content_1_0"><a href="'."http://$param[host]/test/read.cgi/$param[bbs]/$param[key]/".'">'.$_dat[1][title].'</a></h3><br />'."\n";
    $_body .= <<<_HTML_
<table class="thread" border=0 style="table-layout:fixed;width:100%;border:none;border-collapse: collapse;border-width:0;height:1px;position:relative;top:-.4em;border-bottom: 1px solid #ccc;">
  <colgroup>
    <col style="width:30%;" />
    <col style="width:70%;word-wrap:break-word;" />
  </colgroup>
  <thead id="head" style="border-bottom: 1px solid #ccc;">
    <th class="res_info_head" style="width:;">名前</th>
    <th class="res_detele_head" style="width:;">レス</th>
  </thead>
  <tbody>
_HTML_;
    //$resnum = count($_dat);
    //for ($i = 1; $i <= $resnum; $i++) {
    foreach ($_dat as $i => $i_val) {
        if ($i <= 0) {
            continue;
        }
        if ($_dat[$i][num] == 0) {
            continue;
        }
        $_imglink = '';
        if (preg_match_all('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+\.(jpe?g|png|gifv?))', $_dat[$i][text], $result) !== false) {
            foreach ($result[0] as $value) {
                //URL表示
            $_imglink .= '<a href="'.$value.'" target="_break"><img src="'.$value.'" height=20% width=20% alt="'.$value.'" /></a>';
            }
        }
        $_body .= <<<_HTML_
  <tr id="r{$i}" class="res_page">
    <td class="res_info" style="vertical-align:top;">
        {$_dat[$i][num]} : <br />
        <div style="text-align:center;color:#1144aa;"><a href="mailto:{$_dat[$i][mail]}">{$_dat[$i][name]}</a><d/iv>
    </td>
    <td class="res_text" style="width:70%;word-wrap:break-word;">
      {$_dat[$i][text]}
      <br clear="all" />
      <span>
        {$_imglink}
      </span>
    </td>
  </tr>
  <tr style="border-bottom: 1px solid #ccc;">
    <td class="res_id" style="text-align:right;vertical-align:bottom">
      <span style="font-size:x-small;">
        {$_dat[$i][id]}
      </span>
    </td>
    <td class="res_date" style="text-align:right;vertical-align:bottom">
      <span style="font-size:x-small;">
        {$_dat[$i][date]}
      </span>
    </td>
  </tr>
_HTML_;
    }
    $_body .= '</tbody></table>'."\n";
    if (stristr($param[host], '.2ch.')) {
        $_body .= <<<_HTML_
<p style="text-align:right;font-size:3px;"> 引用元：　http://$param[host]/test/read.cgi/$param[bbs]/$param[key]/ </p>
_HTML_;
    } else {/*
        $_body .= <<<_HTML_
<form method="GET" action="http://$param[host]/test/respost.cgi" accept-charset='Shift_JIS' onclick="document.charset='Shift_JIS';">
<input type="hidden" name="ref" value="$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" />
<input type="hidden" name="bbs" value="$param[bbs]" />
<input type="hidden" name="key" value="$param[key]" />
<input type="submit" value="書き込む" />
名前：<input type="text" name="name" style="width:30%;" />
E-mail<font size="1">（省略可）</font>：<input type="text" name="mail" style="width:25%;"><br>
<textarea name="msg" rows="5" style="width:95%;height:0%;"></textarea>
</form>
_HTML_;
    */
    }
  //$_body = htmlsc('TESET PAGE');
  $_body = str_replace(array("\r\n", "\r", "\n"), '', $_body);
    $_body = preg_replace('/>\s+</', '><', $_body);
    return array('msg' => $_dat[1][title].'(v2ch.dat)', 'body' => $_body);
}
function dat2array($datfile, $is_sc)
{
  //$datfile = _get2chdat($host,$bbs,$key);
  $datfile = mb_convert_encoding($datfile, 'utf8', 'sjis-win');//UTFに変換
  $datfile = str_replace(array('<b>', '</b>'), '', $datfile);//余計な</b><b>が入っている場合があるので、削除する
  $datfile = preg_replace('@<a(?:>| [^>]*?>)(.*?)</a>@s', '$1', $datfile);//アンカーのリンクは邪魔なので外す。@はデリミタ
  //各要素をばらす
  preg_match_all('/(.*?)\n/u', $datfile, $lines);//行ごとになっている各レスを独立
  $i = 1;
    foreach ($lines[0] as $_i => $line) {
        ++$_i;
        $line = str_replace(array("\r\n", "\r", "\n"), '', $line);
        $_res_2ch = explode('<>', $line);
  // preg_match_all('/(.*?)<>/u',$line,$elements);//名前、日時、ID、書き込みを各要素別にバラす
  // $res_2ch=array($elements[0]);//foreachの中にforeachを入れたら、なぜか文字化けするので多次元配列に
  // $dat[$i][name]=str_replace('<>','',$res_2ch[0][0]);//名前
  // $dat[$i][mail]=str_replace('<>','',$res_2ch[0][1]);//メルアド
  // $dat[$i][date]=str_replace('<>','',$res_2ch[0][2]);//日時
  // $dat_text=str_replace(' <>','',$res_2ch[0][3]);//本文
  // $dat[$i][title]= str_replace('<>','',$res_2ch[0][4]);//タイトル
  // $res=preg_replace('/^ */','$1',$dat_text);//行頭の半角スペースを削除
  // $dat[$i][text]=preg_replace('/ <br> /','<br />',$res);//半角スペース付き<br>を半角スペースなしの<br />に変換
  $tmp = explode('ID:', $_res_2ch[2]);//日時 ID
  //$is_sc = 0;
  if (!stristr($tmp[1], '.net') && $is_sc) {
      continue;
  }
  //else $dat_array[$i][id] = str_replace('.net', '', $tmp[1]);
        $dat_array[$i][name] = $_res_2ch[0];//名前
        $dat_array[$i][mail] = $_res_2ch[1];//メルアド
        $dat_array[$i][id] = (stristr($tmp[1], '.net')) ?  str_replace('.net', '', $tmp[1]) : $tmp[1];
        $dat_array[$i][num] = (stristr($tmp[1], '.net')) ? $i : 0;
        $dat_array[$i][date] = $tmp[0];
        $dat_array[$i][text] = preg_replace('/ <br> /', '<br />', preg_replace('/^ */', '$1', $_res_2ch[3]));//半角スペース付き<br>を半角スペースなしの<br />に変換
        $dat_array[$i][title] = $_res_2ch[4];//スレタイ
        $dat_array[$i][num] = $_i;
        ++$i;
    }
    return $dat_array;
}
function get2chdat($host, $bbs, $key)
{
    if (!$host && !$bbs && !$key) {
        return; // 変数が足りないなら終了。
    }
  // 初期化
  $host; // サーバ名
  $bbs; // 板名
  $_key = $key;
    $key .= '.dat'; // datファイル名
  $datcache = PLUGIN_2CHDAT_CACHE."{$host}_{$bbs}_{$_key}".'.dat'; // ローカルに保存してあるdatファイルのパス
  $data = '';
    $ch = curl_init();
  // dat取得用header生成
  $header[] = 'GET /'.$bbs.'/dat/'.$key.' HTTP/1.1';
    $header[] = 'Host: '.$host;
    $header[] = 'User-Agent: Monazilla/1.00 (datGetter/ver 1.0)';// UAはMonazillaを推奨。
    if (file_exists($datcache)) {
        $time = filemtime($datcache);
        $mod = date('r', $time - 3600 * 9);
        $byte = filesize($datcache);
        $header[] = 'If-Modified-Since: '.$mod;
        $header[] = 'Range: bytes='.$byte.'-';
    } else {
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    }
    $header[] = 'Connection: close';
  // curlいろいろ
  curl_setopt($ch, CURLOPT_URL, 'http://'.$host.'/'.$bbs.'/dat/'.$key);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FILETIME, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_FILETIME, 1);
  // dat取得
  $data = curl_exec($ch);
  // httpcode取得
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  // Last-Modified取得
  $mod = curl_getinfo($ch, CURLINFO_FILETIME);
  // Last-Modifiedをタイムスタンプに変換
  $t = strtotime($mod);
    if ($t !== false) {
        $mod = $t;
    }
  // とりあえずerrorを取得
  $error = curl_error($ch);
    curl_close($ch);
    file_put_contents($datcache, $data, FILE_APPEND);
    return  file_get_contents($datcache);
}