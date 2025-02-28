<html lang="ja_jp">
<?php

$url = "https://wikic.ga/";
$urlsendpassword = "WikiC_GR8EdCqU3nMR";
function setSimplePluginHtml($loginid,$plgid,$plugname,$plugweb){
    if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){$echo1 = "checked";}else{$echo1='';}
    return '
                '.$plugname.'(<a href="'.$plugweb.'">'.$plgid.'.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="'.$plgid.'__inc__php_plg" name="'.$plgid.'__inc__php_plg" '.$echo1.' />
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />';
}
function loadPlugin($plgid,$wiki_dir){
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
}

//ini_set( 'display_errors', 1 );
function phpsecialchars($chars){
    $chars = str_replace("\\","\\\\",$chars);
    return $chars;
}
function download($pPath, $pName, $pMimeType = null)
{
    if (!is_readable($pPath)) { die($pPath); }
    $mimeType = (isset($pMimeType)) ? $pMimeType
                                    : (new finfo(FILEINFO_MIME_TYPE))->file($pPath);
    if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
        $mimeType = 'application/octet-stream';
    }
    header('Content-Type: ' . $mimeType);
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . filesize($pPath));
    header('Content-Disposition: attachment; filename="' . $pName . '"');
    header('Connection: close');
    while (ob_get_level()) { ob_end_clean(); }
    readfile($pPath);
    exit;
}
function fileMgrDirFileSearch($dirpath, $loginid)
{
    foreach (new DirectoryIterator('./'.$loginid."/".$dirpath) as $dirfileinfo)
    {
        if ($dirfileinfo->isFile())
        {
            $filename = $dirfileinfo->getFilename();
            echo "<a href=\"./?controlpanel&fileeditor=".'./'.$loginid."/".$dirpath.'/'.$filename."\">".$dirpath.'/'.$filename."</a><br />";
        }
        if ($dirfileinfo->isDir())
        {
            $filename = $dirfileinfo->getFilename();
            //fileMgrDirFileSearch($dirpath.'/'.$filename, $loginid);
        }
    }
}
function ksid()
{
    return md5(md5(uniqid('', true).uniqid('', true).uniqid('', true).rand(0, 9999999999).substr(md5('k2e9e3pt6'), 0, 10)).rand(0, 9999999999));
}
function get_ip_address()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
}
function pass_encryption($endata) {
    $method = 'aes-128-cbc';
    $encryption_key　= "53616c7465645f5f39931176af0d7cbb5b4a74b2650e514c37552d34fb1d1a62";
    $encrypted = openssl_encrypt($endata, $method, $encryption_key);
    return $encrypted;
}

function pass_decryption($dedata) {
    $method = 'aes-128-cbc';
    $encryption_key　= "53616c7465645f5f39931176af0d7cbb5b4a74b2650e514c37552d34fb1d1a62";
    $decrypted = openssl_decrypt($dedata, $method, $encryption_key);
    return $decrypted;
}
if (isset($_POST['wikicplogout'])){
    session_start();
    unset($_SESSION['cwiki_loginid']);
    unset($_SESSION['cwikilogin_password']);
    unset($_SESSION['cwiki_pvid']);
    header("location: ./?controlpanel");
    exit;
}
if (isset($_POST['wikicpdelete'])){
    echo "本当にWikiを退会・削除してもいいですか？(Wikiは自動的にバックアップされ非公開、ログイン不可となります。<br />再開する、もしくは完全消去するなら、ログインIDとパスワードを記載してお問い合わせしてください。※対応できない場合もありますのでこの操作は気を付けてください。)<br />この機能は完成してません。";
    exit;
    
}
if (isset($_POST['wikicplogin'])){
    if ($_POST["loginid"] != null){
        if ($_POST["password"] != null){
            $data = null;
            $Logged_in_successfully = "false";
            $split_data = null;
            $file_handler = fopen("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt",r);
            while($data = fgets($file_handler)){
                if (preg_match('/\''.$_POST["loginid"].'\'/',$data)){
                    $tmp_split_data = preg_split( '/\'/', $data);
                    if (pass_encryption($_POST["password"])==$tmp_split_data[1]){
                        $split_data = preg_split( '/\'/', $data);
                        $Logged_in_successfully = "true";
                    }
                }
            }
            fclose($file_handler);
            if ($Logged_in_successfully == "true"){
                session_start();
                $_SESSION['cwiki_loginid'] = pass_encryption($split_data[5]);
                $_SESSION['cwikilogin_password'] = $split_data[1];
                $_SESSION['cwiki_pvid'] = pass_encryption($split_data[13]);
                header("location: ./?controlpanel");
                exit;
            }else{echo "<h2>エラー:Wiki IDまたはパスワードが間違っている可能性があります。</h2>";}
        }else{echo "<h2>エラー:パスワードが入力されていません。</h2>";}
    }else{echo "<h2>エラー:Wiki IDが入力されていません。</h2>";}
}
if (isset($_GET['controlpanel'])) {
    $page = "controlpanel";
}
if (isset($_GET['policy_and_terms'])) {
    $page = "policy_and_terms";
}
if (empty($_GET)||isset($_GET['i'])){
    $page = "top";
}
session_start();
$wiki_fullpath = "";
if (isset($_SESSION['cwiki_loginid'])){
    $wiki_path = pass_decryption($_SESSION['cwiki_loginid']);
    if (file_exists("./".$wiki_path."/pukiwiki.ini.php")){
        $wiki_fullpath = "dir";
    }else{
        $wiki_fullpath = "sub";
    }
}
if (isset($_POST['wikicpsettings1save'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    $_POST['line_break'] = htmlspecialchars($_POST['line_break'], ENT_QUOTES);
    $_POST['line_break'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['line_break']);
    $_POST['multilinepluginhack'] = htmlspecialchars($_POST['multilinepluginhack'], ENT_QUOTES);
    $_POST['multilinepluginhack'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['multilinepluginhack']);
    $_POST['nowikiname'] = htmlspecialchars($_POST['nowikiname'], ENT_QUOTES);
    $_POST['nowikiname'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['nowikiname']);
    $_POST['wiki_website'] = htmlspecialchars($_POST['wiki_website'], ENT_QUOTES);
    $_POST['wiki_website'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['wiki_website']);
    $_POST['wiki_website'] = str_replace(' ', '&#x20;', $_POST['wiki_website']);
    $newline_break = $_POST['line_break'];
    $newnowikiname = $_POST['nowikiname'];
    $newmultilinepluginhack = $_POST['multilinepluginhack'];
    $newwebsite = $_POST['wiki_website'];
    $data = null;
    $gm_line = null;
    $split_data = null;
    if($file_handler = fopen("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt",'r')){
    while($data = fgets($file_handler)){
        session_start();
        if (preg_match('/\''.pass_decryption($_SESSION['cwiki_pvid']).'\'/',$data)){
            $split_data = preg_split( '/\'/u', $data);
            $gm_line = "1";
        }else{
            if (isset($gm_line)){
                $datas2 = $datas2 . $data;
            }else{
                $datas1 = $datas1 . $data;
            }
        }
    }
    }else{
        date_default_timezone_set('Asia/Tokyo');
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt","./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/backup/".time()."account.txt");
    }
    fclose($file_handler);
    if ($gm_line == "1"){
        $filedata1 = $datas1."'".$split_data[1]."','".$split_data[3]."','".$split_data[5]."','".$split_data[7]."','".$split_data[9]."','".$split_data[11]."','".$split_data[13]."','".$split_data[15]."','".$newline_break."','".$newnowikiname."','".$newwebsite."','".$newmultilinepluginhack."'\n".$datas2;
        file_put_contents("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt",$filedata1);
        session_start();
        if ($wiki_fullpath=="dir"){
            $filedata = file_get_contents('./'.$split_data[5].'/pukiwiki.ini.php');
        }else{
            $filedata = urlSend("http://".$split_data[5].".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
        }
        $filedata = str_replace('$line_break = '.$split_data[17].';', '$line_break = '.$newline_break.';', $filedata);
        $filedata = str_replace('$nowikiname = '.$split_data[19].';', '$nowikiname = '.$newnowikiname.';', $filedata);
        $filedata = str_replace('$modifierlink = \''.$split_data[21].'\';', '$modifierlink = \''.$newwebsite.'\';', $filedata);
        $filedata = str_replace('define(\'PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK\', '.$split_data[23].'); // 1 = Disabled', 'define(\'PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK\', '.$newmultilinepluginhack.'); // 1 = Disabled', $filedata);
        if ((isset($_POST['login_authentication']))&&(preg_match('/\$read_auth\s=\s(\d+);/s',$filedata,$matches))){
            $filedata = str_replace($matches[0],'$read_auth = '.$_POST['login_authentication'].';',$filedata);
        }
        if ((isset($_POST['edit_login_authentication']))&&(preg_match('/\$edit_auth\s=\s(\d+);/s',$filedata,$matches))){
            $filedata = str_replace($matches[0],'$edit_auth = '.$_POST['edit_login_authentication'].';',$filedata);
        }
        if ((isset($_POST['user_auth_username']))&&(preg_match('/\$auth_users\s=\sarray\((.+?)\);/s',$filedata,$matches))){
            $inFile = "";
            $for_count = 0;
            foreach ($_POST['user_auth_username'] as $value1) {
                if ($value1 != ""){
                    $value2 = $_POST['user_auth_password'][$for_count];
                    if ($value2 != ""){
                        $value1 = htmlspecialchars($value1, ENT_QUOTES);
                        $value1 = phpsecialchars($value1);
                        $value2 = htmlspecialchars($value2, ENT_QUOTES);
                        $value2 = phpsecialchars($value2);
                        $inFile .= '\''.$value1.'\'=>\''.$value2.'\','."\n";
                    }
                }
                $for_count = $for_count + 1;
            }
            $filedata = str_replace($matches[0],'$auth_users = array('."\n".$inFile.');',$filedata);
        }
        if ((isset($_POST['read_auth_pages_page']))&&(preg_match('/\$read_auth_pages\s=\sarray\((.+?)\);/s',$filedata,$matches))){
            $inFile = "";
            $for_count = 0;
            foreach ($_POST['read_auth_pages_page'] as $value1) {
                if ($value1 != ""){
                    $value2 = $_POST['read_auth_pages_username'][$for_count];
                    if ($value2 != ""){
                        $value1 = htmlspecialchars($value1, ENT_QUOTES);
                        $value1 = phpsecialchars($value1);
                        $value2 = htmlspecialchars($value2, ENT_QUOTES);
                        $value2 = phpsecialchars($value2);
                        $inFile .= '\''.$value1.'\'=>\''.$value2.'\','."\n";
                    }
                }
                $for_count = $for_count + 1;
            }
            $filedata = str_replace($matches[0],'$read_auth_pages = array('."\n".$inFile.');',$filedata);
        }
        if ((isset($_POST['edit_auth_pages_page']))&&(preg_match('/\$edit_auth_pages\s=\sarray\((.+?)\);/s',$filedata,$matches))){
            $inFile = "";
            $for_count = 0;
            foreach ($_POST['edit_auth_pages_page'] as $value1) {
                if ($value1 != ""){
                    $value2 = $_POST['edit_auth_pages_username'][$for_count];
                    if ($value2 != ""){
                        $value1 = htmlspecialchars($value1, ENT_QUOTES);
                        $value1 = phpsecialchars($value1);
                        $value2 = htmlspecialchars($value2, ENT_QUOTES);
                        $value2 = phpsecialchars($value2);
                        $inFile .= '\''.$value1.'\'=>\''.$value2.'\','."\n";
                    }
                }
                $for_count = $for_count + 1;
            }
            $filedata = str_replace($matches[0],'$edit_auth_pages = array('."\n".$inFile.');',$filedata);
        }
        if ($wiki_fullpath=="dir"){
            file_put_contents('./'.$split_data[5].'/pukiwiki.ini.php', $filedata);
        }else{
            urlSend("http://".$split_data[5].".wikic.ga/wikic/putfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword,'data' => $filedata));
        }
    }
}
if (isset($_POST['wikicppagemgrsave'])){
    session_start();
    $loginid = pass_decryption($_SESSION['cwiki_loginid']);
    if ($wiki_fullpath=="dir"){
        $pagefilepath = './'.$loginid.'/wiki/'.$_GET['pageeditor'];
        if (file_exists($pagefilepath)){
            file_put_contents($pagefilepath,$_POST['pageeditor_savedata']);
        }else{
            echo "エラー:ファイルが存在しません。";
            exit;
        }
    }else{
        if (urlSend("http://".$loginid.".wikic.ga/wikic/file_exists.php",array('file' => 'wiki/'.$_GET['pageeditor'],'pass' => $urlsendpassword)) == "false"){
            echo "エラー:ファイルが存在しません。";
            exit;    
        }else{
            urlSend("http://".$loginid.".wikic.ga/wikic/putfiledata.php",array('file' => 'wiki/'.$_GET['pageeditor'],'pass' => $urlsendpassword,'data' => $_POST['pageeditor_savedata']));
        }
    }
}
if (isset($_POST['wikicppagemgrcancel'])){
    header("location: ./?controlpanel");
}

if (isset($_POST['wikicpfilemgrsave'])){
    session_start();
    $loginid = pass_decryption($_SESSION['cwiki_loginid']);
    $pagefilepath = './'.$loginid.'/'.$_GET['fileeditor'];
    $ext = pathinfo($pagefilepath)['extension'];
    if ( $ext == "css" || $ext == "txt" ){
        file_put_contents($pagefilepath,$_POST['fileeditor_savedata']);
    }else{
        echo "エラー:そのファイルの操作は許可されていません。";
    }
}
if (isset($_POST['wikicpfilemgrcancel'])){
    header("location: ./?controlpanel");
}

if (isset($_POST['wikicpsave'])){
    $_POST['wiki_title'] = htmlspecialchars($_POST['wiki_title'], ENT_QUOTES);
    $_POST['wiki_title'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['wiki_title']);    
    $_POST['wiki_adminname'] = htmlspecialchars($_POST['wiki_adminname'], ENT_QUOTES);
    $_POST['wiki_adminname'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['wiki_adminname']);
    $_POST['wiki_freezepass'] = htmlspecialchars($_POST['wiki_freezepass'], ENT_QUOTES);
    $_POST['wiki_freezepass'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['wiki_freezepass']);
    $_POST['wiki_loginpass'] = htmlspecialchars($_POST['wiki_loginpass'], ENT_QUOTES);
    $_POST['wiki_loginpass'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['wiki_loginpass']);
    $_POST['wiki_title'] = str_replace(' ', '&#x20;', $_POST['wiki_title']);
    $_POST['wiki_adminname'] = str_replace(' ', '&#x20;', $_POST['wiki_adminname']);
    $_POST['wiki_freezepass'] = str_replace(' ', '&#x20;', $_POST['wiki_freezepass']);
    $_POST['wiki_loginpass'] = str_replace(' ', '&#x20;', $_POST['wiki_loginpass']);
    $newtitle = $_POST['wiki_title'];
    $newadminname = $_POST['wiki_adminname'];
    $newfreezepass = pass_encryption($_POST['wiki_freezepass']);
    $newloginpass = pass_encryption($_POST['wiki_loginpass']);
    $data = null;
    $gm_line = null;
    $split_data = null;
    $file_handler = fopen("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt",r);
    while($data = fgets($file_handler)){
        session_start();
        if (preg_match('/\''.pass_decryption($_SESSION['cwiki_pvid']).'\'/',$data)){
            $split_data = preg_split( '/\'/', $data);
            $gm_line = "1";
        }else{
            if (isset($gm_line)){
                $datas2 = $datas2 . $data;
            }else{
                $datas1 = $datas1 . $data;
            }
        }
    }
    fclose($file_handler);
    if ($gm_line == "1"){
        $file_handlew = fopen("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt",'w');
        fwrite($file_handlew,$datas1."'".$newloginpass."','".$newadminname."','".$split_data[5]."','".$newtitle."','".$split_data[9]."','".$split_data[11]."','".$split_data[13]."','".$newfreezepass."','".$split_data[17]."','".$split_data[19]."','".$split_data[21]."','".$split_data[23]."'\n".$datas2);
        fclose($file_handlew);
        session_start();
        $_SESSION['cwikilogin_password'] = $newloginpass;
        if ($wiki_fullpath=="dir"){
            $filedata = file_get_contents('./'.$split_data[5].'/pukiwiki.ini.php');
        }else{
            $filedata = urlSend("http://".$split_data[5].".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
        }
        $filedata = str_replace('$modifier = \''.$split_data[3].'\';', '$modifier = \''.$newadminname.'\';', $filedata);
        $filedata = str_replace('$page_title = \''.$split_data[7].'\';', '$page_title = \''.$newtitle.'\';', $filedata);
        $filedata = str_replace('$adminpass = \'{x-php-md5}\' . md5(\''.pass_decryption($split_data[15]).'\');', '$adminpass = \'{x-php-md5}\' . md5(\''.$_POST['wiki_freezepass'].'\');', $filedata);
        if ($wiki_fullpath=="dir"){
            file_put_contents('./'.$split_data[5].'/pukiwiki.ini.php', $filedata);
        }else{
            urlSend("http://".$split_data[5].".wikic.ga/wikic/putfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword,'data' => $filedata));
        }
    }
}
if (isset($_POST['wikicpdesigncss1save'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    if ($wiki_fullpath=="dir"){
        file_put_contents("./".$wiki_dir."/skin/pukiwiki.css", $_POST['css']);
    }else{
        urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'skin/pukiwiki.css','pass' => $urlsendpassword,'data' => $_POST['css']));
    }
}
if (isset($_POST['wikicpdesigncss1reset'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/css/pukiwiki.css","./".$wiki_dir."/skin/pukiwiki.css");
}
if (isset($_POST['wikicpdesigniconfile1save'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    move_uploaded_file($_FILES['iconfile']['tmp_name'],"./".$wiki_dir."/image/pukiwiki.png");
}
if (isset($_POST['wikicpdesigniconfile1reset'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/image/pukiwiki.png","./".$wiki_dir."/image/pukiwiki.png");
}
if (isset($_POST['wikicpdesignfaviconfile1save'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    move_uploaded_file($_FILES['faviconfile']['tmp_name'],"./".$wiki_dir."/image/favicon.png");
    if ($wiki_fullpath=="dir"){
        $filedataurl = file_get_contents("./".$wiki_dir."/skin/pukiwiki.skin.php");
    }else{
        $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword));
    }
    $filedata = preg_replace('/\$_IMAGE\[\'skin\'\]\[\'favicon\'\]\s\s=\s(.+?);/u','$_IMAGE[\'skin\'][\'favicon\']  = \'image/favicon.png\';',$filedataurl);
    if ($wiki_fullpath=="dir"){
        file_put_contents("./".$wiki_dir."/skin/pukiwiki.skin.php", $filedata);
    }else{
        urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword,'data' => $filedata));
    }
}
if (isset($_POST['wikicpdesignfaviconfile1reset'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    unlink("./".$wiki_dir."/image/favicon.png");
    if ($wiki_fullpath=="dir"){
        $filedataurl = file_get_contents("./".$wiki_dir."/skin/pukiwiki.skin.php");
    }else{
        $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword));
    }
    $filedata = preg_replace('/\$_IMAGE\[\'skin\'\]\[\'favicon\'\]\s\s=\s(.+?);/u','$_IMAGE[\'skin\'][\'favicon\']  = \'\';',$filedataurl);
    if ($wiki_fullpath=="dir"){
        file_put_contents("./".$wiki_dir."/skin/pukiwiki.skin.php", $filedata);
    }else{
        urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword,'data' => $filedata));
    }
}
if (isset($_POST['wikicpplgsettings1save'])){
    session_start();
    $wiki_dir = pass_decryption($_SESSION['cwiki_loginid']);
    if ($_POST['youtube__inc__php__k_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/youtube.inc.php","./".$wiki_dir."/plugin/youtube.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/youtube.inc.php");
    }
    if ($_POST['nicovideo_player__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/nicovideo_player.inc.php","./".$wiki_dir."/plugin/nicovideo_player.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/nicovideo_player.inc.php");
    }
    if ($_POST['attachref__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/attachref.inc.php","./".$wiki_dir."/plugin/attachref.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/attachref.inc.php");
    }
    if ($_POST['ogp__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/ogp.inc.php","./".$wiki_dir."/plugin/ogp.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/ogp.inc.php");
    }
    if ($_POST['bgcolor__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/bgcolor.inc.php","./".$wiki_dir."/plugin/bgcolor.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/bgcolor.inc.php");
    }
    if ($_POST['commentplus__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/commentplus.inc.php","./".$wiki_dir."/plugin/commentplus.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/commentplus.inc.php");
    }
    if ($_POST['emphasis__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/emphasis.inc.php","./".$wiki_dir."/plugin/emphasis.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/emphasis.inc.php");
    }
    if ($_POST['soundcloud__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/soundcloud.inc.php","./".$wiki_dir."/plugin/soundcloud.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/soundcloud.inc.php");
    }
    if ($_POST['totalpages__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/totalpages.inc.php","./".$wiki_dir."/plugin/totalpages.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/totalpages.inc.php");
    }
    if ($_POST['twitter_embed__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/twitter_embed.inc.php","./".$wiki_dir."/plugin/twitter_embed.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/twitter_embed.inc.php");
    }
    if ($_POST['img64__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/img64.inc.php","./".$wiki_dir."/plugin/img64.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/img64.inc.php");
    }
    if ($_POST['steam__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/steam.inc.php","./".$wiki_dir."/plugin/steam.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/steam.inc.php");
    }
    if ($_POST['fahstats__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/fahstats.inc.php","./".$wiki_dir."/plugin/fahstats.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/fahstats.inc.php");
    }
    if ($_POST['readingtime__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/readingtime.inc.php","./".$wiki_dir."/plugin/readingtime.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/readingtime.inc.php");
    }
    if ($_POST['v2chdat__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/v2chdat.inc.php","./".$wiki_dir."/plugin/v2chdat.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/v2chdat.inc.php");
    }
    if ($_POST['v2chdat__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/v2chdat.inc.php","./".$wiki_dir."/plugin/v2chdat.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/v2chdat.inc.php");
    }
    if ($_POST['region__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/region.inc.php","./".$wiki_dir."/plugin/region.inc.php");
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/endregion.inc.php","./".$wiki_dir."/plugin/endregion.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/region.inc.php");
        unlink("./".$wiki_dir."/plugin/endregion.inc.php");
    }
    if ($_POST['regiongroup__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/regiongroup.inc.php","./".$wiki_dir."/plugin/regiongroup.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/regiongroup.inc.php");
    }
    if ($_POST['tableedit__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/tableedit.inc.php","./".$wiki_dir."/plugin/tableedit.inc.php");
        if ($wiki_fullpath=="dir"){
            $filedataurl = file_get_contents("./".$wiki_dir."/cache/.htaccess");
        }else{
            $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'cache/.htaccess','pass' => $urlsendpassword));
        }
        $htadata = $filedataurl;
        if (strpos($htadata,"<FilesMatch \"^tableedit.*\.html$\">\nRequire all granted\n</FilesMatch>\n")==true){
        }else{
            $file_handlea = fopen("./".$wiki_dir."/cache/.htaccess","a");
            fwrite($file_handlea,"<FilesMatch \"^tableedit.*\.html$\">\nRequire all granted\n</FilesMatch>\n");
            fclose($file_handlea);
        }
    }else{
        unlink("./".$wiki_dir."/plugin/tableedit.inc.php");
        if ($wiki_fullpath=="dir"){
            $filedataurl = file_get_contents("./".$wiki_dir."/cache/.htaccess");
        }else{
            $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'cache/.htaccess','pass' => $urlsendpassword));
        }
        if (strpos($htadata,"<FilesMatch \"^tableedit.*\.html$\">\nRequire all granted\n</FilesMatch>\n")==true){
            str_replace("<FilesMatch \"^tableedit.*\.html$\">\nRequire all granted\n</FilesMatch>\n","",$htadata);
            if ($wiki_fullpath=="dir"){
        		file_put_contents("./".$wiki_dir."/cache/.htaccess",$htadata);
    		}else{
   		     	urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'cache/.htaccess','pass' => $urlsendpassword,'data' => $htadata));
    		}
        }
    }
    if ($_POST['divregion__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/divregion.inc.php","./".$wiki_dir."/plugin/divregion.inc.php");
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/enddivregion.inc.php","./".$wiki_dir."/plugin/enddivregion.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/divregion.inc.php");
        unlink("./".$wiki_dir."/plugin/enddivregion.inc.php");
    }
    if ($_POST['twintent__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/twintent.inc.php","./".$wiki_dir."/plugin/twintent.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/twintent.inc.php");
    }
    if ($_POST['addline__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/addline.inc.php","./".$wiki_dir."/plugin/addline.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/addline.inc.php");
    }
    if ($_POST['areaedit__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/areaedit.inc.php","./".$wiki_dir."/plugin/areaedit.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/areaedit.inc.php");
    }
    if ($_POST['exkp__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/exkp.inc.php","./".$wiki_dir."/plugin/exkp.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/exkp.inc.php");
    }
    if ($_POST['gotaku__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/gotaku.inc.php","./".$wiki_dir."/plugin/gotaku.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/gotaku.inc.php");
    }
    if ($_POST['jumplist__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/jumplist.inc.php","./".$wiki_dir."/plugin/jumplist.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/jumplist.inc.php");
    }
    if ($_POST['ls2_1__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/ls2_1.inc.php","./".$wiki_dir."/plugin/ls2_1.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/ls2_1.inc.php");
    }
    if ($_POST['marquee__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/marquee.inc.php","./".$wiki_dir."/plugin/marquee.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/marquee.inc.php");
    }
    if ($_POST['shadowheader__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/shadowheader.inc.php","./".$wiki_dir."/plugin/shadowheader.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/shadowheader.inc.php");
    }
    if ($_POST['sizex__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/sizex.inc.php","./".$wiki_dir."/plugin/sizex.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/sizex.inc.php");
    }
    if ($_POST['submenu__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/submenu.inc.php","./".$wiki_dir."/plugin/submenu.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/submenu.inc.php");
    }
    if ($_POST['tag__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/tag.inc.php","./".$wiki_dir."/plugin/tag.inc.php");
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/tagcloud.inc.php","./".$wiki_dir."/plugin/tagcloud.inc.php");
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/taglist.inc.php","./".$wiki_dir."/plugin/taglist.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/tag.inc.php");
        unlink("./".$wiki_dir."/plugin/tagcloud.inc.php");
        unlink("./".$wiki_dir."/plugin/taglist.inc.php");
    }
    if ($_POST['tooltip__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/tooltip.inc.php","./".$wiki_dir."/plugin/tooltip.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/tooltip.inc.php");
    }
    if ($_POST['tvote__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/tvote.inc.php","./".$wiki_dir."/plugin/tvote.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/tvote.inc.php");
    }
    if ($_POST['vote2__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/vote2.inc.php","./".$wiki_dir."/plugin/vote2.inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/vote2.inc.php");
    }
    $plgid = "discord";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "votex";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "replaceplugin";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "articleplus";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/commentplus.inc.php","./".$wiki_dir."/plugin/commentplus.inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "html";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "qrcode";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "easyedit";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        $copy_directory = "./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/easyedit";
        if ($handle = opendir("./".$wiki_dir."/easyedit")) {
            while(false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    copy($entry, $copy_directory . $entry);
                }
            }
            closedir($handle);
        }
        if ($wiki_fullpath=="dir"){
            $filedataurl = file_get_contents("./".$wiki_dir."/skin/pukiwiki.skin.php");
        }else{
            $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword));
        }
        if (preg_match("/<\?php\sglobal\s\$vars;echo\s'<a\shref=\"\./\?cmd=easyedit&page='\.\$vars\['page'\]\.'\">編集\(CKEditor\)<\/a>';\s\?>\s\|/u",$filematch,$filedataurl)){
        }else{
            if ($wiki_fullpath=="dir"){
                $filedataurl = file_get_contents("./".$wiki_dir."/skin/pukiwiki.skin.php");
            }else{
                $filedataurl = urlSend("http://".$wiki_dir.".wikic.ga/wikic/getfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword));
            }
            $savefile = str_replace('<?php _navigator(\'edit\') ?> |','<?php _navigator(\'edit\') ?> |'."\n".'<?php global $vars;echo \'<a href="./?cmd=easyedit&page=\'.$vars[\'page\'].\'">編集(CKEditor)</a>\'; ?> |',$filedataurl);
            if ($wiki_fullpath=="dir"){
        		file_put_contents("./".$wiki_dir."/skin/pukiwiki.skin.php",$savefile);
    		}else{
   		     	urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword,'data' => $savefile));
    		}
        }
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        if($savefile = str_replace('<?php global $vars;echo \'<a href="./?cmd=easyedit&page=\'.$vars[\'page\'].\'">編集(CKEditor)</a>\'; ?> |','',file_get_contents("./".$wiki_dir."/skin/pukiwiki.skin.php","a"))){
            if ($wiki_fullpath=="dir"){
        		file_put_contents("./".$wiki_dir."/skin/pukiwiki.skin.php",$savefile);
    		}else{
   		     	urlSend("http://".$wiki_dir.".wikic.ga/wikic/putfiledata.php",array('file' => 'skin/pukiwiki.skin.php','pass' => $urlsendpassword,'data' => $savefile));
    		}
        }
    }
    $plgid = "archive";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "timestamp";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "pukiwikitimes";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "rothtml";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "age";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "button";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "null";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "viewedit";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "google_site_translate";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "pluglist";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "google";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "yahoo";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "yahoojp";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "youtube_sr";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "viewedit_writever";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/pukiedit.inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/viewedit-writever.inc.php","./".$wiki_dir."/plugin/edit.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/pukiedit.inc.php","./".$wiki_dir."/plugin/pukiedit.inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/edit.inc.php");
        unlink("./".$wiki_dir."/plugin/pukiedit.inc.php");
        copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/edit.inc.php","./".$wiki_dir."/plugin/edit.inc.php");
    }
    $plgid = "submit";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "alert";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "theme";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "googlemaps2";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_draw.inc.php","./".$wiki_dir."/plugin/".$plgid."_draw.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_mark.inc.php","./".$wiki_dir."/plugin/".$plgid."_mark.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_insertmarker.inc.php","./".$wiki_dir."/plugin/".$plgid."_insertmarker.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_icon.inc.php","./".$wiki_dir."/plugin/".$plgid."_icon.inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_draw.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_mark.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_insertmarker.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_icon.inc.php");
    }
    $plgid = "googlemaps3";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_draw.inc.php","./".$wiki_dir."/plugin/".$plgid."_draw.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_mark.inc.php","./".$wiki_dir."/plugin/".$plgid."_mark.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_insertmarker.inc.php","./".$wiki_dir."/plugin/".$plgid."_insertmarker.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid."_icon.inc.php","./".$wiki_dir."/plugin/".$plgid."_icon.inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_draw.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_mark.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_insertmarker.inc.php");
        unlink("./".$wiki_dir."/plugin/".$plgid."_icon.inc.php");
    }
    $plgid = "urlbookmark";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "whatday";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "pagetree";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "fusen";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "checkbox";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "timestamp_backup";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "manageform";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "html_cache";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "select_navi";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "google_analytics";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "memo2";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "code";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/pre.inc.php","./".$wiki_dir."/plugin/pre.inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/code/","./".$wiki_dir."/plugin/code/");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        unlink("./".$wiki_dir."/plugin/pre.inc.php");
        unlink("./".$wiki_dir."/plugin/code/");
    }
    $plgid = "tab";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "sortable_table";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    $plgid = "mlcomment";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
    }
    loadPlugin("youtube2",$wiki_dir);
    loadPlugin("alias",$wiki_dir);
    loadPlugin("expand",$wiki_dir);
    loadPlugin("slideshow",$wiki_dir);
    loadPlugin("twitter",$wiki_dir);
    loadPlugin("aomsig",$wiki_dir);
    loadPlugin("url",$wiki_dir);
    loadPlugin("mathjax",$wiki_dir);
    loadPlugin("mlarticle",$wiki_dir);
    loadPlugin("style",$wiki_dir);
    loadPlugin("html2pdf",$wiki_dir);
    loadPlugin("regexp",$wiki_dir);
    loadPlugin("heading5",$wiki_dir);
    loadPlugin("region2",$wiki_dir);
    loadPlugin("accordion",$wiki_dir);
    loadPlugin("seimei",$wiki_dir);
    loadPlugin("randommes",$wiki_dir);
    loadPlugin("redirect",$wiki_dir);
    loadPlugin("mail",$wiki_dir);
    loadPlugin("whatsnew",$wiki_dir);
    loadPlugin("countdown",$wiki_dir);
    loadPlugin("topicpath",$wiki_dir);
    loadPlugin("tex2",$wiki_dir);
    loadPlugin("tex",$wiki_dir);
    loadPlugin("raty",$wiki_dir);
    loadPlugin("jschart",$wiki_dir);
    loadPlugin("ifreadable",$wiki_dir);
    loadPlugin("relatedlist",$wiki_dir);
    loadPlugin("title",$wiki_dir);
    loadPlugin("gyo2cal",$wiki_dir);
    loadPlugin("recentdetail",$wiki_dir);
    loadPlugin("sub",$wiki_dir);
    loadPlugin("sup",$wiki_dir);
    $plgid = "freeze2";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/unfreeze2.inc.php","./".$wiki_dir."/plugin/unfreeze2.inc.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        unlink("./".$wiki_dir."/plugin/unfreeze2.inc.php");
    }
    loadPlugin("deldel",$wiki_dir);
    loadPlugin("ctrlcmt",$wiki_dir);
    loadPlugin("latest_pages",$wiki_dir);
    loadPlugin("bcontents",$wiki_dir);
    loadPlugin("brecent",$wiki_dir);
    loadPlugin("blink",$wiki_dir);
    loadPlugin("aframe360",$wiki_dir);
    loadPlugin("epre",$wiki_dir);
    loadPlugin("ecache",$wiki_dir);
    loadPlugin("enull",$wiki_dir);
    loadPlugin("splitbody",$wiki_dir);
    loadPlugin("exrules2",$wiki_dir);
    loadPlugin("webdl",$wiki_dir);
    loadPlugin("infobox",$wiki_dir);
    loadPlugin("todo",$wiki_dir);
    loadPlugin("tasks",$wiki_dir);
    loadPlugin("markdown",$wiki_dir);
    loadPlugin("ul",$wiki_dir);
    loadPlugin("bar",$wiki_dir);
    $plgid = "card";
    if ($_POST[$plgid.'__inc__php_plg']=="on"){
        if (!file_exists("./".$wiki_dir."/plugin/".$plgid.".inc.php")){
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/".$plgid.".inc.php","./".$wiki_dir."/plugin/".$plgid.".inc.php");
            copy("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/resize.php","./".$wiki_dir."/plugin/resize.php");
        }
    }else{
        unlink("./".$wiki_dir."/plugin/".$plgid.".inc.php");
        unlink("./".$wiki_dir."/plugin/resize.php");
    }
}
if (isset($_POST['createwiki'])) {
    if ($_POST['dir'] != null) {
        if ($_POST['password'] != null) {
            $user_dup = 'false';
            $_POST['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES);
            $_POST['title'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['title']);
            $_POST['username'] = htmlspecialchars($_POST['username'], ENT_QUOTES);
            $_POST['username'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['username']);
            $_POST['dir'] = htmlspecialchars($_POST['dir'], ENT_QUOTES);
            $_POST['dir'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['dir']);
            $_POST['password'] = htmlspecialchars($_POST['password'], ENT_QUOTES);
            $_POST['password'] = preg_replace('/\\r\\n|\\n|\\r/', '<br />', $_POST['password']);
            $_POST['title'] = str_replace(' ', '&#x20;', $_POST['title']);
            $_POST['username'] = str_replace(' ', '&#x20;', $_POST['username']);
            $_POST['password'] = str_replace(' ', '&#x20;', $_POST['password']);
            $encryptedpassword = pass_encryption($_POST['password']);
            $encryptedip = pass_encryption(get_ip_address());
            $split_data = null;
            $file_handler = fopen('./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt', r);
            while ($data = fgets($file_handler)) {
                if (preg_match('/\''.$_POST['dir'].'\'/', $data)) {
                    $user_dup = 'true';
                }
            }
            fclose($file_handler);
            if ($user_dup == 'false') {
                $file_handlea = fopen('./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt', a);
                fwrite($file_handlea, "'".$encryptedpassword."','".$_POST['username']."','".$_POST['dir']."','".$_POST['title']."','".$_POST['type']."','".$encryptedip."','".ksid()."','".$encryptedpassword."','"."1"."','"."1"."','".$url."','"."1"."'\n");//17 break 19 nowikiname
                fclose($file_handlea);
                echo '<h3>正常にアカウントを登録しました。</h3>';
                $zip = new ZipArchive();
                if (file_exists('./'.$_POST['dir'].'/')) {
                    echo '<h3>既にそのWiki IDは使われています。</h3>';
                } else {
                    if ($_POST['type'] == 'pukiwiki') {
                        $wiki_zipfile = 'pukiwiki.zip';
                    }
                    if ($_POST['type'] == 'dokuwiki') {
                        $wiki_zipfile = 'dokuwiki.zip';
                    }
                    if ($zip->open('./'.$wiki_zipfile) == true) {
                        $zip->extractTo('./'.$_POST['dir'].'/');
                        $zip->close();
                        if ($_POST['type'] == 'pukiwiki') {
                            if ($wiki_fullpath=="dir" && $wiki_fullpath==null){
                                $filedataurl = file_get_contents("./".$_POST['dir']."/pukiwiki.ini.php");
                            }else{
                                $filedataurl = file_get_contents("./".$_POST['dir']."/pukiwiki.ini.php");
                                //$filedataurl = urlSend("http://".$_POST['dir'].".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
                            }
                            $filedata = $filedataurl;
                            $filedata = str_replace('$modifier = \'anonymous\';', '$modifier = \''.$_POST['username'].'\';', $filedata);
                            $filedata = str_replace('$modifierlink = \'http://pukiwiki.example.com/\';', '$modifierlink = \''.$url.'\';', $filedata);
                            $filedata = str_replace('$page_title = \'PukiWiki\';', '$page_title = \''.$_POST['title'].'\';', $filedata);
                            $filedata = str_replace('$adminpass = \'{x-php-md5}!\';', '$adminpass = \'{x-php-md5}\' . md5(\''.$_POST['password'].'\');', $filedata);
                            $filedata = str_replace('$line_break = 0;', '$line_break = 1;', $filedata);
                            $filedata = str_replace('$nowikiname = 0;', '$nowikiname = 1;', $filedata);
                            file_put_contents('./'.$_POST['dir'].'/pukiwiki.ini.php', $filedata);
                            echo '<h3>Wikiが生成されました。<a href="'.$url.$_POST['dir'].'/">'.$url.$_POST['dir'].'/</a></h3>';
                        }
                        if ($_POST['type'] == 'dokuwiki') {
                            echo '<h3>Wikiが生成されました。セットアップ画面→<a href="'.$url.$_POST['dir'].'/install.php">'.$url.$_POST['dir'].'/install.php</a></h3>';
                        }
                    } else {
                        echo '<h3>失敗しました。再度お試しください。</h3>';
                    }
                }
            }
        } else {
            echo '<h2>エラー:パスワードが入力されていません。</h2>';
        }
    } else {
        echo '<h2>エラー:Wiki IDが入力されていません。</h2>';
    }
}

?>
<?php if ($page=="top"){ ?>

    <head>
        <title>WikiC</title>
        <meta name="google-site-verification" content="A-ZImhqrc13c7cRvCTk5RwMI9BB_6YMxiw_uHWmoAfo" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
        <meta name="google-site-verification" content="op3TOg8JuSGy-WMBvDLa8x-Ro9MjKykuX_eNAbq6jrM" />
    </head>
    <body>
        <font size="8px"><pre>高度なカスタマイズが可能 無料 高機能 PukiWiki生成サイト<br />   <strong>WikiC</strong>(WikiCreator)</pre></font>
        <font size="5px"><a name="wikilogin"><h2><a href="./?controlpanel" style="color:#444444;">Wikiのコントロールパネルへログイン</a></h2></a></font>
        <font size="5px"><h2><a href="#createwiki" style="color:#444444;">Wiki作成</a></h2></font>
        <font size="5px"><h2><a href="#updatedwikis" style="color:#444444;">更新されたWiki</a></h2></font>
        <font size="5px"><h2><a href="#first" style="color:#444444;">お初の方へ</a></h2></font>
        <font size="5px"><h2><a href="#other" style="color:#444444;">その他</a></h2></font>

        <a name="createwiki"><h2>Wiki作成</h2></a>
        <form method="post">
            Wiki名:<input type="text" name="title" id="title" placeholder="Exampleウィキ" value="PukiWiki"></input>
            <br />
            Wiki ID(ログインID):<input type="text" name="dir" id="dir" placeholder="examplewiki"></input>
            <br />
            管理人:<input type="text" name="username" id="username" value="anonymous"></input>
            <br />
            パスワード(ログイン用、凍結用):<input type="password" name="password" id="password" placeholder="パスワード"></input>
            <br />
            ソフトウェア:<select id="type" name="type">
                <option value="pukiwiki" selected>PukiWiki</option>
                <option value="dokuwiki">DokuWiki</option>
            </select>
            <br />
            <input type="submit" name="createwiki" id="createwiki" value="Wiki作成"></input>
            <br />
            ソフトウェアによってはWikiの作成に少し時間がかかる可能性があります。<br />
            PukiWikiを推奨します。(現在コントロールパネルはPukiWikiのみサポート)
        </form>
        <a name="updatedwikis"><h2>更新されたWiki</h2></a>
<?php
$wikifiles = [];
$i = 0;
foreach (new DirectoryIterator('./') as $fileinfo) {
    if($i >= 50){
        break;
    }
    if (!$fileinfo->isDot()) {
        $filename = $fileinfo->getFilename();
        if ($filename != "53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0" && $filename != "index.php" && $filename != "debug12480143.php" && $filename != "pukiwiki.zip" && $filename != "dokuwiki.zip" && $filename != ".htaccess"){
            $data = null;$split_data = null;$split_data2 = null;
            $file_handler = fopen('./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt', r);
            while ($data = fgets($file_handler)) {
                $split_data2 = preg_split('/\'/', $data);
                if ($filename == $split_data2[5]){
                    $split_data = preg_split('/\'/', $data);
                }
            }
            fclose($file_handler);
            if ($split_data[9] == "pukiwiki"){
                $wikifiles[] = ['name' => $filename, 'date' => filemtime($filename."/wiki")];
            }else if ($split_data[9] == "dokuwiki"){
                $wikifiles[] = ['name' => $filename, 'date' => filemtime($filename."/data/pages/wiki")];
            }else{}
            $i++;
        }
    }
}
$latestwikifiles = [];
foreach($wikifiles as $key => $value){
  $latestwikifiles[$key] = $value['date'];
}
array_multisort($latestwikifiles, SORT_DESC, $wikifiles);
foreach($wikifiles as $key => $value){
    $data = null;$split_data = null;$split_data2 = null;
    $file_handler = fopen('./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt', r);
    while ($data = fgets($file_handler)) {
        $split_data2 = preg_split('/\'/', $data);
        if ($value['name'] == $split_data2[5]){
            $split_data = preg_split('/\'/', $data);
        }
    }
    fclose($file_handler);
    if (mb_strlen($split_data[7]) >= 25){
        $split_data[7] = mb_substr($split_data[7], 0, 25) . "...";
    }
    echo "<font size=\"5\"><a href=\"./".$value['name']."/\" style=\"color:black;display:inline-block;\">".$split_data[7]."</a></font> <font color=\"#666666\">".date("Y/m/d H:i:s",$value['date'])."</font><br />";
}
?>
    <a name="first"><h2>Wiki紹介</h2></a>
        <font size="5"><a href="./amongus/" style="color:#FF8866;display:inline-block;">AmongUs Wiki</a></font><br />
        <font size="5"><a href="./rk/" style="color:#FF8866;display:inline-block;">コミュニティ</a></font>

    <a name="first"><h2>お初の方へ</h2></a>
        <font size="5"><a href="./SampleWiki/" style="color:#FF8866;display:inline-block;">サンプルWiki(使い方Wiki)</a></font>
        <br />
        <font size="5"><a href="./TrialWiki/" style="color:#66DDFF;display:inline-block;">お試しWiki</a></font>
        <br />
        <font size="5"><a href="./?policy_and_terms" style="color:#FF3333;display:inline-block;">規約と方針</a></font>
    <a name="other"><h2>その他</h2></a>
        <font size="5"><a href="./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plg/">プラグイン一覧＆アーカイブ</a></font>
        <br />
        <font size="5"><a href="http://pkom.ml/">WikiC管理人のWebサイト</a></font>
    </body>
<?php } ?>
<?php 
//function
function urlSend($sendurl,$POST_DATA){
    $conn = curl_init();
    if (isset($POST_DATA)){
        curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($POST_DATA));
    }
    curl_setopt($conn, CURLOPT_URL, $sendurl);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($conn);
    curl_close($conn);
    return $html;
}

?>
<?php if ($page == "policy_and_terms"){
?>
<style>
h1,h2 {
    width:30%;
    margin:auto;
}
ul {
    width:50%;
    margin:auto;
}
p {
    width:50%;
    margin:auto;
}
</style>
<body>
<h1>規約と方針</h1>
<h2>利用規約</h2>
<ul>
    <li>当サイトの管理人の指示は必ずしも従ってください。</li>
    <li>危険性のあるWikiがあれば、管理人のメール(admin@pkom2.ml)までご連絡ください。</li>
    <li>法に触れるようなWikiの作成は禁止します。</li>
    <li>他人を誹謗するようなWiki・ページは<strong>勝手に削除</strong>させて頂くことがあります。</li>
    <li>アダルトは可能とします。ただし、R-18コンテンツのあるWiki・ページは閲覧者がR-18のWiki・ページだということがわかるようにしてください。</li>
    <li>R-18要素のないWikiに管理者の許可を得ずにエロ画像・グロ画像を貼る行為は原則として禁止します。</li>
    <li>また、特定の活動は規制します。例:侮辱行為、害悪行為(荒らし行為、スパム行為)、虚偽記載、詐欺行為、不正利用(ウイルスや悪意のあるコードを配布)に関するのWiki、児童ポルノに関するWiki、他者へのプライバシー侵害したり権利を侵害するようなWiki</li>
</ul>
<h2>免責事項</h2>
<p>免責事項に書かれていることは、法に触れない限り、<strong>一切責任を負いません。</strong></p>
<ul>
    <li>当サイトで生成されたWikiなどの配布物は<strong>管理しません。</strong><br />ダウンロード・扱いは<strong>自己責任</strong>でお願いします。</li>
    <li>何れかのWikiで不利益が生じても、<strong>責任は負いません。</strong><br />そのWiki作者に問い合わせてください。</li>
</ul>
</body>
<?php } ?>
<?php if ($page == "controlpanel"){
    session_start();
    //if ($_SESSION['cwiki_loginid'])
    //$_SESSION['cwikilogin_password']
    if (isset($_SESSION['cwiki_pvid'])){
        $loginid = pass_decryption($_SESSION['cwiki_loginid']);
        $loginid_password = pass_decryption($_SESSION['cwikilogin_password']);
        $pvid = pass_decryption($_SESSION['cwiki_pvid']);
        $data = null;
        $loaded = "false";
        $split_data = null;
        $file_handler = fopen("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/account.txt","r");
        while($data = fgets($file_handler)){
            if (preg_match('/\''.$pvid.'\'/',$data)){
                $split_data = preg_split( '/\'/', $data);
                $loaded = "true";
            }
        }
        fclose($file_handler);
        if ($loaded=="true"){
            $title = $split_data[7];
            $adminname = $split_data[3];
            $freeze_password = pass_decryption($split_data[15]);
            $setted_line_break = $split_data[17];
            $setted_nowikiname = $split_data[19];
            $adminwebsite = $split_data[21];
            $setted_multilinepluginhack = $split_data[23];
            if ($wiki_fullpath == "dir"){
                preg_match('/\$read_auth\s=\s(\d+);/s',file_get_contents('./'.$loginid.'/pukiwiki.ini.php'),$matches);$setted_login_authentication = $matches[1];
                preg_match('/\$edit_auth\s=\s(\d+);/s',file_get_contents('./'.$loginid.'/pukiwiki.ini.php'),$matches);$setted_edit_login_authentication = $matches[1];
                $theme_css = file_get_contents("./".$loginid."/skin/pukiwiki.css");
            }else{
                $urlfiledata = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
                preg_match('/\$read_auth\s=\s(\d+);/s',$urlfiledata,$matches);$setted_login_authentication = $matches[1];
                preg_match('/\$edit_auth\s=\s(\d+);/s',$urlfiledata,$matches);$setted_edit_login_authentication = $matches[1];
                $theme_css = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => 'skin/pukiwiki.css','pass' => $urlsendpassword));
            }
        }
        if ($_GET['pageeditor']){
            if ($wiki_fullpath == "dir"){
                $pagefilepath = './'.$loginid.'/wiki/'.$_GET['pageeditor'];
                $pagedata = file_get_contents($pagefilepath);
            }else{
                $pagefilepath = 'wiki/'.$_GET['pageeditor'];
                $pagedata = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => $pagefilepath,'pass' => $urlsendpassword));
            }
?>
    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
        <link rel="stylesheet" type="text/css" href="./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/switch.css">
        <title>ページエディター - WikiC</title>
    </head>
    <body>
    <h3><?php echo hex2bin(pathinfo($_GET['pageeditor'])['filename']); ?></h3>
        <form method="post">
            <textarea name="pageeditor_savedata" id="pageeditor_savedata" style="width:100%;height:85%;"><?php echo $pagedata; ?></textarea>
            <input type="submit" name="wikicppagemgrsave" id="wikicppagemgrsave" value="保存"></input>&nbsp;<input type="submit" name="wikicppagemgrcancel" id="wikicppagemgrcancel" value="閉じる"></input>
            <br />※閉じる前に保存ボタンを押さないとページが保存されません

        </form>
    </body>
<?php
        }else if($_GET['fileeditor']){
            $pagefilepath = './'.$loginid.'/'.$_GET['fileeditor'];
            $ext = pathinfo($pagefilepath)['extension'];
            if ( $ext == "css" || $ext == "txt" ){
                $pagedata = file_get_contents($pagefilepath);
            }else{
                echo "エラー:そのファイルの操作は許可されていません。";
                exit;
            }
            
?>
    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
        <link rel="stylesheet" type="text/css" href="./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/switch.css">
        <title>ファイルエディター - WikiC</title>
    </head>
    <body>
    <h3><?php echo $_GET['fileeditor']; ?></h3>
        <form method="post">
            <textarea name="fileeditor_savedata" id="fileeditor_savedata" style="width:100%;height:85%;"><?php echo $pagedata; ?></textarea>
            <input type="submit" name="wikicpfilemgrsave" id="wikicpfilemgrsave" value="保存"></input>&nbsp;<input type="submit" name="wikicpfilemgrcancel" id="wikicpfilemgrcancel" value="閉じる"></input>
            <br />※閉じる前に保存ボタンを押さないとファイルが保存されません
        </form>
    </body>
<?php  
        }else if(isset($_GET['pagedownloader'])){
            $fpath = './'.$loginid.'/wiki/'.$_GET['pagedownloader'];
            $fname = hex2bin(pathinfo($_GET['pagedownloader'])['filename']).'.txt';
            download($fpath,$fname);
            exit;
        }else if(isset($_GET['filelist'])){
            include("./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/system/filelist.php");
            exit;
        }else if(isset($_GET['filedelete'])){
            unlink("./" . $loginid . "/" . $_GET['filedelete']);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }else{
        ?>

    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
        <link rel="stylesheet" type="text/css" href="./53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/switch.css">
        <title>コントロールパネル - WikiC</title>
    </head>
    <body>
        v0.5.1-β
        <br />
        現在コントロールパネルはPukiWikiのみサポートしています。
        <br />
        URL:<a href="<?php echo $url.$loginid."/" ?>"><?php echo $url.$loginid."/" ?></a>
        <br />
        <?php //echo realpath("./".$loginid."/pukiwiki.ini.php") ?>
        <h2>管理人設定</h2>
        <form method="post">
        <script type="text/javascript">var wikipagemgr_hide=0;var wikifilemgr_hide=0;var wikidesigncss_hide=0;var change_title_hide=0;var change_freezepass_hide=0;var change_loginpass_hide=0;var change_adminname_hide=0;var wikisettings_hide=0;var change_website_hide=0;var wikiplgsettings_hide=0;var wikiuser_auth_hide=0;var wikiupdate_hide=0</script>
        Wiki名:<div id="change_title1" style="display: inline-block;"><?php echo $title; ?></div><div id="change_title2" style="display: none;"><input id="wiki_title" name="wiki_title" type="text" value="<?php echo $title; ?>"/></div> <a href="javascript:if(change_title_hide == 0){document.getElementById('change_title1').style.display='none';document.getElementById('change_title2').style.display='inline-block';change_title_hide=1;}else if(change_title_hide == 1){document.getElementById('change_title2').style.display='none';document.getElementById('change_title1').style.display='inline-block';document.getElementById('change_title1').innerHTML=document.getElementById('wiki_title').value;change_title_hide=0;}">[変更]</a>
        <br />
        管理人名(Site admin):<div id="change_adminname1" style="display: inline-block;"><?php echo $adminname; ?></div><div id="change_adminname2" style="display: none;"><input id="wiki_adminname" name="wiki_adminname" type="text" value="<?php echo $adminname; ?>"/></div> <a href="javascript:if(change_adminname_hide == 0){document.getElementById('change_adminname1').style.display='none';document.getElementById('change_adminname2').style.display='inline-block';change_adminname_hide=1;}else if(change_adminname_hide == 1){document.getElementById('change_adminname2').style.display='none';document.getElementById('change_adminname1').style.display='inline-block';document.getElementById('change_adminname1').innerHTML=document.getElementById('wiki_adminname').value;change_adminname_hide=0;}">[変更]</a>
        <br />
        パスワード(Wiki):<div id="change_freezepass1" style="display: inline-block;"><?php echo $freeze_password; ?></div><div id="change_freezepass2" style="display: none;"><input id="wiki_freezepass" name="wiki_freezepass" type="password" value="<?php echo $freeze_password; ?>"/></div> <a href="javascript:if(change_freezepass_hide == 0){document.getElementById('change_freezepass1').style.display='none';document.getElementById('change_freezepass2').style.display='inline-block';change_freezepass_hide=1;}else if(change_freezepass_hide == 1){document.getElementById('change_freezepass2').style.display='none';document.getElementById('change_freezepass1').style.display='inline-block';document.getElementById('change_freezepass1').innerHTML=document.getElementById('wiki_freezepass').value;change_freezepass_hide=0;}">[変更]</a>
        <br />
        パスワード(コントロールパネル):<div id="change_loginpass1" style="display: inline-block;"><?php echo $loginid_password; ?></div><div id="change_loginpass2" style="display: none;"><input id="wiki_loginpass" name="wiki_loginpass" type="password" value="<?php echo $loginid_password; ?>"/></div> <a href="javascript:if(change_loginpass_hide == 0){document.getElementById('change_loginpass1').style.display='none';document.getElementById('change_loginpass2').style.display='inline-block';change_loginpass_hide=1;}else if(change_loginpass_hide == 1){document.getElementById('change_loginpass2').style.display='none';document.getElementById('change_loginpass1').style.display='inline-block';document.getElementById('change_loginpass1').innerHTML=document.getElementById('wiki_loginpass').value;change_loginpass_hide=0;}">[変更]</a>
        <br />
            <input type="submit" name="wikicpsave" id="wikicpsave" value="適用"></input>
        </form>
        <form method="post">
        <input type="submit" name="wikicplogout" id="wikicplogout" value="ログアウト"></input>
        </form>
        <form method="post">
        <input type="submit" name="wikicpdelete" id="wikicpdelete" value="Wikiの退会(削除)"></input>
        </form>
<a href="javascript:if(wikisettings_hide == 0){document.getElementById('wikisettings1').style.display='none';document.getElementById('wikisettings2').style.display='inline-block';document.getElementById('wikisettingsopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>Wiki詳細設定&nbsp;</ele>[-]</h2>';wikisettings_hide=1;}else if(wikisettings_hide == 1){document.getElementById('wikisettings2').style.display='none';document.getElementById('wikisettings1').style.display='inline-block';document.getElementById('wikisettingsopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>Wiki詳細設定&nbsp;</ele>[+]</h2>';wikisettings_hide=0;}" id="wikisettingsopen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >Wiki詳細設定&nbsp;</ele>[+]</h2></a>
        <div id="wikisettings1" style="display: inline-block;"></div>
        <div id="wikisettings2" style="display: none;">
            <form method="post">
                編集時「~」を使って改行させる($line_break):
                <select id="line_break" name="line_break">
                    <option value="0" <?php if ($setted_line_break == 0){echo "selected";} ?>>有効</option>
                    <option value="1" <?php if ($setted_line_break == 1){echo "selected";} ?>>無効</option>
                </select>
                <br />
                大文字小文字を混ぜた英文字列のWikiName($nowikiname):
                <select id="nowikiname" name="nowikiname">
                    <option value="0" <?php if ($setted_nowikiname == 0){echo "selected";} ?>>有効</option>
                    <option value="1" <?php if ($setted_nowikiname == 1){echo "selected";} ?>>無効</option>
                </select>
                <br />
                マルチラインプラグインハック:
                <select id="multilinepluginhack" name="multilinepluginhack">
                    <option value="0" <?php if ($setted_multilinepluginhack == 0){echo "selected";} ?>>有効</option>
                    <option value="1" <?php if ($setted_multilinepluginhack == 1){echo "selected";} ?>>無効</option>
                </select>
                <br />
                閲覧制限(ユーザー認証):
                <select id="login_authentication" name="login_authentication">
                    <option value="1" <?php if ($setted_login_authentication == 1){echo "selected";} ?>>有効</option>
                    <option value="0" <?php if ($setted_login_authentication == 0){echo "selected";} ?>>無効</option>
                </select>
                <br />
                編集制限(ユーザー認証):
                <select id="edit_login_authentication" name="edit_login_authentication">
                    <option value="1" <?php if ($setted_edit_login_authentication == 1){echo "selected";} ?>>有効</option>
                    <option value="0" <?php if ($setted_edit_login_authentication == 0){echo "selected";} ?>>無効</option>
                </select>
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:if(wikiuser_auth_hide == 0){document.getElementById('wikiuser_auth1').style.display='none';document.getElementById('wikiuser_auth2').style.display='inline-block';document.getElementById('wikiuser_authopen').innerHTML='<h3><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ユーザー認証設定&nbsp;</ele>[-]</h3>';wikiuser_auth_hide=1;}else if(wikiuser_auth_hide == 1){document.getElementById('wikiuser_auth2').style.display='none';document.getElementById('wikiuser_auth1').style.display='inline-block';document.getElementById('wikiuser_authopen').innerHTML='<h3><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ユーザー認証設定&nbsp;</ele>[+]</h3>';wikiuser_auth_hide=0;}" id="wikiuser_authopen"><h3><ele style="text-decoration:none;color:black;display:inline-block;" >ユーザー認証設定&nbsp;</ele>[+]</h3></a>
                <div id="wikiuser_auth1" style="display: inline-block;"></div>
                <div id="wikiuser_auth2" style="display: none;">
                PukiWikiのユーザー認証機能であり、WikiCコントロールパネルでの設定以外、WikiCとは関連していません。
                <br />
                凍結しなくても、ユーザー認証で制限を掛けることができます。
                <h4>ユーザー設定</h4>
                ユーザーを追加、削除、編集できます。
                    <hr />
                    <span id="userauthofuserarea">
<?php
$for_count = 0;
if ($wiki_fullpath=="dir"){
    $filedata = file_get_contents('./'.$loginid.'/pukiwiki.ini.php');
}else{
    $filedata = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
}
if (preg_match('/\$auth_users\s=\sarray\((.+?)\);/s',$filedata,$matches)){
    $inHTML = "";preg_match_all('/\'(.+?)\'=>\'(.+?)\',/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $for_count = $for_count + 1;
        $inHTML .= '<ele id="user_auth_ele'.$for_count.'"><input type="text" name="user_auth_username[]" id="user_auth_username'.$for_count.'" value="'.$value1[1].'" size="10" placeholder="ユーザー名"> <input type="text" name="user_auth_password[]" id="user_auth_password'.$for_count.'" value="'.$value1[2].'" size="10" placeholder="パスワード"> <input type="button" name="user_auth_delete'.$for_count.'" id="user_auth_delete'.$for_count.'" onclick="RemoveUserAuthOfUser('.$for_count.');" value="削除"><br /></ele>';
    }
    echo $inHTML;
}
?>                    
                    </span>
                    <input type="button" value="追加" onClick="AddUserAuthOfUser();" />
<script>
var aao_c1 = <?php echo $for_count; ?>;
var userinput1 = document.getElementById('userauthofuserarea');
function AddUserAuthOfUser(){
	aao_c1++;
	userinput1.insertAdjacentHTML('beforeend','<ele id="user_auth_ele'+aao_c1+'"><input type="text" name="user_auth_username[]" id="user_auth_username' + aao_c1 + '" value="" size="10" placeholder="ユーザー名" /> <input type="text" name="user_auth_password[]" id="user_auth_password' + aao_c1 + '" value="" size="10" placeholder="パスワード"/> <input type="button" name="user_auth_delete'+aao_c1+'" id="user_auth_delete'+aao_c1+'" onClick="RemoveUserAuthOfUser('+aao_c1+');" value="削除" /><br /></ele>');
}
function RemoveUserAuthOfUser(userauthareaid){
    document.getElementById('user_auth_ele' + userauthareaid).remove();
}
</script>
                <hr />
                <h4>閲覧制限ページ</h4>
                設定したページを設定されたユーザーのみが閲覧できるようにします。<br />
                ※ログインしていない人や、設定されてないユーザー名は設定したページを閲覧できないようになります。<br />
                ※この機能は「閲覧制限(ユーザー認証)」と「ユーザー設定」をしないと利用できません。<br />
                設定の方法は<a href="https://pukiwiki.osdn.jp/?PukiWiki/Install/%E3%83%A6%E3%83%BC%E3%82%B6%E8%AA%8D%E8%A8%BC">PukiWiki公式サイト - ユーザー認証</a>をご覧ください。<br />
                また、ここでのページ名の設定は 例:「#(FrontPage|MenuBar)#」のように枠内へ設定する必要があります。(※ページ名は「#」で囲んでください。複数指定は「()」で囲み、「|」で区切ってください。)<br />
                ユーザー名はユーザー設定の閲覧できるようにしたいユーザー名を設定してください。(複数指定は「,」で区切ってください。)<br />
                    <hr />
                    <span id="readuserauthofuserarea">
<?php
$for_count = 0;
if ($wiki_fullpath=="dir"){
    $filedata = file_get_contents('./'.$loginid.'/pukiwiki.ini.php');
}else{
    $filedata = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
}
if (preg_match('/\$read_auth_pages\s=\sarray\((.+?)\);/s',$filedata,$matches)){
    $inHTML = "";preg_match_all('/\'(.+?)\'=>\'(.+?)\',/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $for_count = $for_count + 1;
        $inHTML .= '<ele id="read_user_auth_ele'.$for_count.'"><input type="text" name="read_auth_pages_page[]" id="read_auth_pages_page'.$for_count.'" value="'.$value1[1].'" size="20" placeholder="ページ名"> <input type="text" name="read_auth_pages_username[]" id="read_auth_pages_username'.$for_count.'" value="'.$value1[2].'" size="10" placeholder="ユーザー名"> <input type="button" name="read_user_auth_delete'.$for_count.'" id="read_user_auth_delete'.$for_count.'" onclick="RemoveReadUserAuthOfUser('.$for_count.');" value="削除"><br /></ele>';
    }
    echo $inHTML;
}
?>                    
                    </span>
                    <input type="button" value="追加" onClick="AddReadUserAuthOfUser();" />
<script>
var aao_c2 = <?php echo $for_count; ?>;
var userinput2 = document.getElementById('readuserauthofuserarea');
function AddReadUserAuthOfUser(){
	aao_c2++;
	userinput2.insertAdjacentHTML('beforeend','<ele id="read_user_auth_ele'+aao_c2+'"><input type="text" name="read_auth_pages_page[]" id="read_auth_pages_page' + aao_c2 + '" value="" size="20" placeholder="ページ名" /> <input type="text" name="read_auth_pages_username[]" id="read_auth_pages_username' + aao_c2 + '" value="" size="10" placeholder="ユーザー名"/> <input type="button" name="read_user_auth_delete'+aao_c2+'" id="read_user_auth_delete'+aao_c2+'" onClick="RemoveReadUserAuthOfUser('+aao_c2+');" value="削除" /><br /></ele>');
}
function RemoveReadUserAuthOfUser(userauthareaid){
    document.getElementById('read_user_auth_ele' + userauthareaid).remove();
}
</script>
                <hr />
                <h4>編集制限ページ</h4>
                設定したページを設定されたユーザーのみが編集できるようにします。<br />
                ※ログインしていない人や、設定されてないユーザー名は設定したページを編集できないようになります。<br />
                ※この機能は「編集制限(ユーザー認証)」と「ユーザー設定」をしないと利用できません。<br />
                設定の方法は<a href="https://pukiwiki.osdn.jp/?PukiWiki/Install/%E3%83%A6%E3%83%BC%E3%82%B6%E8%AA%8D%E8%A8%BC">PukiWiki公式サイト - ユーザー認証</a>をご覧ください。<br />
                また、ここでのページ名の設定は 例:「#(FrontPage|MenuBar)#」のように枠内へ設定する必要があります。(※ページ名は「#」で囲んでください。複数指定は「()」で囲み、「|」で区切ってください。)<br />
                ユーザー名はユーザー設定の編集できるようにしたいユーザー名を設定してください。(複数指定は「,」で区切ってください。)<br />
                    <hr />
                    <span id="edituserauthofuserarea">
<?php
$for_count = 0;
if ($wiki_fullpath=="dir"){
    $filedata = file_get_contents('./'.$loginid.'/pukiwiki.ini.php');
}else{
    $filedata = urlSend("http://".$loginid.".wikic.ga/wikic/getfiledata.php",array('file' => 'pukiwiki.ini.php','pass' => $urlsendpassword));
}
if (preg_match('/\$edit_auth_pages\s=\sarray\((.+?)\);/s',$filedata,$matches)){
    $inHTML = "";preg_match_all('/\'(.+?)\'=>\'(.+?)\',/u',$matches[1],$matches2,PREG_SET_ORDER);
    foreach($matches2 as $value1)
    {
        $for_count = $for_count + 1;
        $inHTML .= '<ele id="edit_user_auth_ele'.$for_count.'"><input type="text" name="edit_auth_pages_page[]" id="edit_auth_pages_page'.$for_count.'" value="'.$value1[1].'" size="20" placeholder="ページ名"> <input type="text" name="edit_auth_pages_username[]" id="edit_auth_pages_username'.$for_count.'" value="'.$value1[2].'" size="10" placeholder="ユーザー名"> <input type="button" name="edit_user_auth_delete'.$for_count.'" id="edit_user_auth_delete'.$for_count.'" onclick="RemoveEditUserAuthOfUser('.$for_count.');" value="削除"><br /></ele>';
    }
    echo $inHTML;
}
?>                    
                    </span>
                    <input type="button" value="追加" onClick="AddEditUserAuthOfUser();" />
<script>
var aao_c3 = <?php echo $for_count; ?>;
var userinput3 = document.getElementById('edituserauthofuserarea');
function AddEditUserAuthOfUser(){
	aao_c3++;
	userinput3.insertAdjacentHTML('beforeend','<ele id="edit_user_auth_ele'+aao_c3+'"><input type="text" name="edit_auth_pages_page[]" id="edit_auth_pages_page' + aao_c3 + '" value="" size="20" placeholder="ページ名" /> <input type="text" name="edit_auth_pages_username[]" id="edit_auth_pages_username' + aao_c3 + '" value="" size="10" placeholder="ユーザー名"/> <input type="button" name="edit_user_auth_delete'+aao_c3+'" id="edit_user_auth_delete'+aao_c3+'" onClick="RemoveEditUserAuthOfUser('+aao_c3+');" value="削除" /><br /></ele>');
}
function RemoveEditUserAuthOfUser(userauthareaid){
    document.getElementById('edit_user_auth_ele' + userauthareaid).remove();
}
</script>
                <hr />
	            </div>
                <br />
                ウェブサイト:<div id="change_website1" style="display: inline-block;"><?php echo $adminwebsite; ?></div><div id="change_website2" style="display: none;"><input id="wiki_website" name="wiki_website" type="text" value="<?php echo $adminwebsite; ?>"/></div> <a href="javascript:if(change_website_hide == 0){document.getElementById('change_website1').style.display='none';document.getElementById('change_website2').style.display='inline-block';change_website_hide=1;}else if(change_website_hide == 1){document.getElementById('change_website2').style.display='none';document.getElementById('change_website1').style.display='inline-block';document.getElementById('change_website1').innerHTML=document.getElementById('wiki_website').value;change_website_hide=0;}">[変更]</a>
                <br />
                <input type="submit" name="wikicpsettings1save" id="wikicpsettings1save" value="適用"></input>
            </form>
        </div>
        <a href="javascript:if(wikidesigncss_hide == 0){document.getElementById('wikidesigncss1').style.display='none';document.getElementById('wikidesigncss2').style.display='inline-block';document.getElementById('wikidesigncssopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>デザイン設定&nbsp;</ele>[-]</h2>';wikidesigncss_hide=1;}else if(wikidesigncss_hide == 1){document.getElementById('wikidesigncss2').style.display='none';document.getElementById('wikidesigncss1').style.display='inline-block';document.getElementById('wikidesigncssopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>デザイン設定&nbsp;</ele>[+]</h2>';wikidesigncss_hide=0;}" id="wikidesigncssopen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >デザイン設定&nbsp;</ele>[+]</h2></a>
        <div id="wikidesigncss1" style="display: inline-block;"></div>
        <div id="wikidesigncss2" style="display: none;">
            <form method="post">
                <h4>スキン(pukiwiki.css)</h4>
                この機能は上級者向けです。<br />
                <textarea name="css" id="css" cols="100" rows="30"><?php echo $theme_css; ?></textarea>
                <br />
                <input type="submit" name="wikicpdesigncss1save" id="wikicpdesigncss1save" value="保存・適用"></input>&nbsp;<input type="submit" name="wikicpdesigncss1reset" id="wikicpdesigncss1reset" value="リセット"></input>
            </form>
            <script>
            function uploadedIconPreView(event) {
              var file = event.target.files[0];
              var reader = new FileReader();
              var preview = document.getElementById("uploadedIconPreview");
              var previewImage = document.getElementById("previewUploadedIcon");
              if (previewImage != null) {
                preview.removeChild(previewImage);
              }
              reader.onload = function (event) {
                var img = document.createElement("img");
                img.setAttribute("src", reader.result);
                img.setAttribute("id", "previewUploadedIcon");
                preview.appendChild(img);
              };
              reader.readAsDataURL(file);
            }
            function uploadedFaviconPreView(event) {
              var file = event.target.files[0];
              var reader = new FileReader();
              var preview = document.getElementById("uploadedFaviconPreview");
              var previewImage = document.getElementById("previewUploadedFavicon");
              if (previewImage != null) {
                preview.removeChild(previewImage);
              }
              reader.onload = function (event) {
                var img = document.createElement("img");
                img.setAttribute("src", reader.result);
                img.setAttribute("id", "previewUploadedFavicon");
                preview.appendChild(img);
              };
              reader.readAsDataURL(file);
            }
            </script>
            <form method="post" enctype="multipart/form-data">
                <h4>アイコン</h4>
                左上のアイコンを変更できます。 <br />
                <div id="uploadedIconPreview"></div>
                <input type="file" name="iconfile" onChange="uploadedIconPreView(event)"></input><input type="submit" name="wikicpdesigniconfile1save" id="wikicpdesigniconfile1save" value="保存・適用"></input>&nbsp;<input type="submit" name="wikicpdesigniconfile1reset" id="wikicpdesigniconfile1reset" value="リセット"></input>
            </form>
            <form method="post" enctype="multipart/form-data">
                <h4>ファビコン</h4>
                ファビコン(タブなどに表示されるアイコン)を変更できます。<br />
                <div id="uploadedFaviconPreview"></div>
                <input type="file" name="faviconfile" onChange="uploadedFaviconPreView(event)"></input><input type="submit" name="wikicpdesignfaviconfile1save" id="wikicpdesignfaviconfile1save" value="保存・適用"></input>&nbsp;<input type="submit" name="wikicpdesignfaviconfile1reset" id="wikicpdesignfaviconfile1reset" value="リセット"></input>
            </form>
        </div>
        <a href="javascript:if(wikiplgsettings_hide == 0){document.getElementById('wikiplgsettings1').style.display='none';document.getElementById('wikiplgsettings2').style.display='inline-block';document.getElementById('wikiplgsettingsopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>プラグイン設定&nbsp;</ele>[-]</h2>';wikiplgsettings_hide=1;}else if(wikiplgsettings_hide == 1){document.getElementById('wikiplgsettings2').style.display='none';document.getElementById('wikiplgsettings1').style.display='inline-block';document.getElementById('wikiplgsettingsopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>プラグイン設定&nbsp;</ele>[+]</h2>';wikiplgsettings_hide=0;}" id="wikiplgsettingsopen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >プラグイン設定&nbsp;</ele>[+]</h2></a>
        <div id="wikiplgsettings1" style="display: inline-block;"></div>
        <div id="wikiplgsettings2" style="display: none;">
            中には悪用される、不具合があるプラグインが存在する可能性がございます。
            プラグインの更新をするには1回オフして適用し、もう一度オンにして適用してください。
            <form method="post">
            YouTube埋め込みプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/youtube.inc.php.k">youtube.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="youtube__inc__php__k_plg" name="youtube__inc__php__k_plg" <?php if (file_exists("./".$loginid."/plugin/youtube.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ニコニコ動画埋め込みプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/nicovideo_player.inc.php">nicovideo_player.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="nicovideo_player__inc__php_plg" name="nicovideo_player__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/nicovideo_player.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                添付拡張プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/attachref.inc.php">attachref.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="attachref__inc__php_plg" name="attachref__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/attachref.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                機能追加版コメントプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/commentplus.inc.php">commentplus.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="commentplus__inc__php_plg" name="commentplus__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/commentplus.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "articleplus"; ?>
                機能追加版掲示板プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                このプラグインを導入するとcommentplusも同時に導入されます。
                <br />
                ページの背景色・文字色変更プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/bgcolor.inc.php">bgcolor.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="bgcolor__inc__php_plg" name="bgcolor__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/bgcolor.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                圏点表示プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/emphasis.inc.php">emphasis.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="emphasis__inc__php_plg" name="emphasis__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/emphasis.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                SoundCloud埋め込みプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/soundcloud.inc.php">soundcloud.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="soundcloud__inc__php_plg" name="soundcloud__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/soundcloud.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ウィキ内のページ数を表示するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/totalpages.inc.php">totalpages.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="totalpages__inc__php_plg" name="totalpages__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/totalpages.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ツイート埋め込みプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/twitter_embed.inc.php">twitter_embed.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="twitter_embed__inc__php_plg" name="twitter_embed__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/twitter_embed.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                Base64エンコードされた画像を埋め込むプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/img64.inc.php">img64.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="img64__inc__php_plg" name="img64__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/img64.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                Steam埋め込みウィジェットを表示するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/steam.inc.php">steam.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="steam__inc__php_plg" name="steam__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/steam.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                Folding@homeの統計情報を表示するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/fahstats.inc.php">fahstats.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="fahstats__inc__php_plg" name="fahstats__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/fahstats.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                読了時間表示プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/readingtime.inc.php">readingtime.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="readingtime__inc__php_plg" name="readingtime__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/readingtime.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                折りたたみプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/region.inc.php">region.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="region__inc__php_plg" name="region__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/region.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                regionの拡張プラグイン(<a href="http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3%2Fregiongroup">regiongroup.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="regiongroup__inc__php_plg" name="regiongroup__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/regiongroup.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                テーブル編集プラグイン(<a href="http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/tableedit">tableedit.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="tableedit__inc__php_plg" name="tableedit__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/tableedit.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                折りたたみの改造版プラグイン(<a href="http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/divregion">divregion.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="divregion__inc__php_plg" name="divregion__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/divregion.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                Twitterの投稿ボタンを設置するプラグイン(<a href="http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/twintent">twintent.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="twintent__inc__php_plg" name="twintent__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/twintent.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ボタンを押すと指定文字列を追加するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Faddline.inc.php">addline.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="addline__inc__php_plg" name="addline__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/addline.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ページ内の指定領域のみを編集対象とするプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fareaedit.inc.php">areaedit.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="areaedit__inc__php_plg" name="areaedit__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/areaedit.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                携帯とPCとで表示するソースを使い分けるプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fexk.inc.php%E3%81%A8exp.inc.php">exkp.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="exkp__inc__php_plg" name="exkp__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/exkp.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                質問の回答を集計してランク付けする(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fgotaku.inc.php">gotaku.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="gotaku__inc__php_plg" name="gotaku__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/gotaku.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                listboxで選択してページ移動するためのプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fjumplist.inc.php">jumplist.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="jumplist__inc__php_plg" name="jumplist__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/jumplist.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ls2の拡張プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/ls2_1.inc.php">ls2_1.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="ls2_1__inc__php_plg" name="ls2_1__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/ls2_1.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                marqueeを表示するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/marquee.inc.php">marquee.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="marquee__inc__php_plg" name="marquee__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/marquee.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                #contentsに表示されない見出しを書くためのプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/shadowheader.inc.php">shadowheader.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="shadowheader__inc__php_plg" name="shadowheader__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/shadowheader.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                フォントサイズをcssにあるクラスを用いて相対サイズ指定するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/sizex.inc.php">sizex.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="sizex__inc__php_plg" name="sizex__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/sizex.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                現在表示中のページによって、MenuBarに表示する内容を切り替えるプラグイン(<a href="http://pukiwiki.sonots.com/?Plugin%2Fsubmenu.inc.php">submenu.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="submenu__inc__php_plg" name="submenu__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/submenu.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ページにタグを付けるプラグイン(<a href="http://pukiwiki.sonots.com/?Plugin%2Ftag.inc.php">tag.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="tag__inc__php_plg" name="tag__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/tag.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ツールチッププラグイン(<a href="http://project.chu.jp/sha/index.php?tooltip.inc.php">tooltip.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="tooltip__inc__php_plg" name="tooltip__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/tooltip.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ソートされる投票プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/tvote.inc.php">tvote.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="tvote__inc__php_plg" name="tvote__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/tvote.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                インライン型の投票プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fvote2.inc.php">vote2.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="vote2__inc__php_plg" name="vote2__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/vote2.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "discord"; ?>
                Discordガジェット埋め込みプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "votex"; ?>
                [不具合あり]グラフの投票プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "replaceplugin"; ?>
                replaceプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "html"; ?>
                HTMLを直接出力するプラグイン 制限付きバージョン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。
                <br />
                <?php $plgid = "qrcode"; ?>
                QRコードプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "easyedit"; ?>
                CKEditorを使用して編集するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                このプラグインはWikiCのシステムの構成上、CKEditor編集画面では標準のCSSとなります。
                <br />
                <?php $plgid = "archive"; ?>
                外部サイトをアーカイブするプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "timestamp"; ?>
                タイムスタンプを書き換えるプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "button"; ?>
                buttonプラグイン(<a href="https://pukiwiki.osdn.jp/dev/?PukiWiki/1.4/%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/button%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "age"; ?>
                満年齢算出(age)プラグイン(<a href="https://pukiwiki.osdn.jp/dev/?PukiWiki/1.4/%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/age"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "null"; ?>
                常に空文字を返すインラインプラグイン(<a href="https://pukiwiki.osdn.jp/dev/?PukiWiki/1.4/%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/null%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "pukiwikitimes"; ?>
                pukiwikitimes （blogtimes の pukiwiki 版）(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "viewedit"; ?>
                リアルタイムでプレビュー(編集画面拡張)(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "pluglist"; ?>
                プラグイン一覧表示プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/PlugList"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "google_site_translate"; ?>
                PukiWiki用Googleサイト翻訳プラグイン(<a href="https://dajya-ranger.com/sdm_downloads/google-site-translate-plugin/"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "google"; ?>
                Google検索ボックス設置プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/%E6%A4%9C%E7%B4%A2%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9%E8%A8%AD%E7%BD%AE%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "yahoo"; ?>
                海外版Yahoo!検索ボックス設置プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/%E6%A4%9C%E7%B4%A2%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9%E8%A8%AD%E7%BD%AE%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "yahoojp"; ?>
                日本版Yahoo!検索ボックス設置プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/%E6%A4%9C%E7%B4%A2%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9%E8%A8%AD%E7%BD%AE%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "youtube_sr"; ?>
                YouTube検索ボックス設置プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/%E6%A4%9C%E7%B4%A2%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9%E8%A8%AD%E7%BD%AE%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "viewedit_writever"; ?>
                Edit書き換え版リアルタイムでプレビュー(編集画面拡張)＆通常プラグインEdit(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/viewedit.inc.php">edit.inc.php&pukiedit.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/pukiedit.inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                [編集]を押すと自動的にViewEditに切り替わります。
                <br />
                <?php $plgid = "submit"; ?>
                ボタン(Submit版)設置プラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/button.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "alert"; ?>
                JavaScriptを利用して通知するプラグイン(<a href="http://k0.22web.org/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/alert.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "theme"; ?>
                スタイルシート（CSS）を切り替えるプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "googlemaps2"; ?>
                Googleマップ2表示プラグイン(<a href="http://reddog.s35.xrea.com/wiki/Pukiwiki%E3%81%A7GoogleMaps2.html"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "googlemaps3"; ?>
                Googleマップ3表示プラグイン(<a href="http://reddog.s35.xrea.com/wiki/Pukiwiki%E3%81%A7GoogleMaps3.html"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "urlbookmark"; ?>
                URLのブックマークを作るプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "whatday"; ?>
                何の日プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "pagetree"; ?>
                JavaScriptを使わないツリーメニュープラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "fusen"; ?>
                付箋プラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "checkbox"; ?>
                checkboxを手軽につかうプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "timestamp_backup"; ?>
                タイムスタンプを保存したり読み込んだりする引っ越し用プラグイン(<a href="http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/timestamp_backup.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "manageform"; ?>
                PukiWikiManageForm[PukiWiki設定プラグイン](<a href="http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/manageform.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "html_cache"; ?>
                HTML Cache[ページを一時的に保存して少しだけ処理を軽量化するプラグイン](<a href="http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/html_cache.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "select_navi"; ?>
                ナビゲーションバーにセレクトボックスを追加するプラグイン(<a href="http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/select_navi.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "google_analytics"; ?>
                Googleアナリティクスのコードを追加するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "memo2"; ?>
                表示内容に応じてサイズ変更するmemoプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "code"; ?>
                プログラムのソースコードを色分けして表示するプラグイン(<a href="https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/<?php echo $plgid; ?>.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                ※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。
                <br />
                <?php $plgid = "sortable_table"; ?>
                PukiWiki用ソートテーブル（表）プラグイン(<a href="https://dajya-ranger.com/sdm_downloads/sortable-table-plugin/"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "tab"; ?>
                タブ切り替え表示プラグイン(<a href="https://jpngamerswiki.com/?21026aa838"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
                <?php $plgid = "mlcomment"; ?>
                Mluti-lineComment[テキストエリア版のコメントプラグイン](<a href="http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/mlcomment.inc.php"><?php echo $plgid; ?>.inc.php</a>) <div class="switch" style="display: inline-block;">
                    <label class="switch3__label">
                        <input type="checkbox" class="switch3__input" id="<?php echo $plgid; ?>__inc__php_plg" name="<?php echo $plgid; ?>__inc__php_plg" <?php if (file_exists("./".$loginid."/plugin/".$plgid.".inc.php")){echo "checked";} ?>/>
                        <span class="switch3__content"></span>
                        <span class="switch3__circle"></span>
                    </label>
                </div>
                <br />
<?php
echo setSimplePluginHtml($loginid,"accordion","アコーディオンプラグイン[見出し折りたたみプラグイン]","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/accordion.inc.php")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"aframe360","360度パノラマ画像表示プラグイン","http://tomose.dynalias.net/junk/?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/aframe360")
.setSimplePluginHtml($loginid,"alias","指定したページへジャンプする（≒ページに別名を付ける）プラグイン","http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/alias")
.setSimplePluginHtml($loginid,"aomsig","Age of Mythologyプレイヤーシグネチャ画像表示プラグイン","https://dexlab.net/pukiwiki/index.php?Software/wiki%BC%AB%BA%EE%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/aomsig.inc.php")
.setSimplePluginHtml($loginid,"bar","バーグラフ (ゲージ) 表示プラグイン","https://jpngamerswiki.com/?7298051596")
.setSimplePluginHtml($loginid,"bcontents","ブログ風目次プラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/bcontents.inc.php")
.setSimplePluginHtml($loginid,"blink","ブログ風リンクプラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/blink.inc.php")
.setSimplePluginHtml($loginid,"brecent","ブログ風ページ一覧プラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/brecent.inc.php")
.setSimplePluginHtml($loginid,"card","ブログカード風リンク作成プラグイン","https://jpngamerswiki.com/?113d23d852")
.setSimplePluginHtml($loginid,"countdown","カウントダウン（あと何日？）プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/countdown.inc.php")
.setSimplePluginHtml($loginid,"ctrlcmt","管理画面で設定できる機能拡張版コメントプラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/ctrlcmt.inc.php")
.setSimplePluginHtml($loginid,"deldel","複数ページ一括削除プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/deldel.inc.php")
.setSimplePluginHtml($loginid,"ecache","お手軽部分キャッシュプラグイン","http://pukiwiki.sonots.com/?Plugin/ecache.inc.php")
.setSimplePluginHtml($loginid,"enull","Wiki文法を実行するが何も出力しないプラグイン","http://pukiwiki.sonots.com/?Plugin/enull.inc.php")
.setSimplePluginHtml($loginid,"epre","Wiki文法の出力HTMLをpre表示するプラグイン","http://pukiwiki.sonots.com/?Plugin/epre.inc.php")
.setSimplePluginHtml($loginid,"expand","任意の範囲を折りたたみ表示するプラグイン","https://jpngamerswiki.com/?56478a40a9")
.setSimplePluginHtml($loginid,"exrules2","ページ内でユーザ定義ルールを設定するプラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/exrules2.inc.php")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"freeze2","複数ページ一括凍結/凍結解除プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/freeze2.inc.php%E3%81%A8unfreeze2.inc.php")
.setSimplePluginHtml($loginid,"gyo2cal","２行カレンダ","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/gyo2cal.inc.php")
.setSimplePluginHtml($loginid,"heading5","見出し(h5)プラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3")
.setSimplePluginHtml($loginid,"html2pdf","PDF 生成プラグイン","http://pukiwiki.sonots.com/?Plugin%2Fhtml2pdf.inc.php")
.setSimplePluginHtml($loginid,"ifreadable","ページの権限を調べるプラグイン","https://wiki.dobon.net/index.php?PukiWiki%2F%BC%AB%BA%EE%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3%2Fifreadable.inc.php")
.setSimplePluginHtml($loginid,"infobox","wikipediaの右上の書式風の整形ボックスを出力するプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/infobox.inc.php")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"jschart","JSChartを利用したグラフ表示プラグイン","http://www.ark-web.jp/sandbox/wiki/206.html")
.setSimplePluginHtml($loginid,"latest_pages","ブログ風ページ一覧プラグイン(現brecent)","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/latest_pages.inc.php")
.setSimplePluginHtml($loginid,"mail","メールの送信フォームプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/mail.inc.php")
.setSimplePluginHtml($loginid,"markdown","Markdown記法が使えるプラグイン","https://github.com/shuuji3/pukiwiki-plugin-markdown")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"mathjax","JavaScript版数式プラグイン","https://dev.abicky.net/pukiwiki/plugins/index.php?mathjax.inc.php")
.setSimplePluginHtml($loginid,"mlarticle","mlcomment版掲示板プラグイン","")
.setSimplePluginHtml($loginid,"randommes","指定したファイル or ページにかかれている一行メッセージをランダムに表示するプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/randommes.inc.php")
.setSimplePluginHtml($loginid,"raty","5段階評価を付けるプラグイン","https://oxynotes.com/?p=10360")
.setSimplePluginHtml($loginid,"recentdetail","更新の時期別にグループ表示できるrecentプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/recentdetail.inc.php")
.setSimplePluginHtml($loginid,"redirect","リダイレクトプラグイン[指定ページにリダイレクト(自動転送)するプラグイン]","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/redirect.inc.php")
.setSimplePluginHtml($loginid,"regexp","正規表現による文字列置換プラグイン（プレビュー付き）","http://pukiwiki.sonots.com/?Plugin%2Fregexp.inc.php")
.setSimplePluginHtml($loginid,"region2","折りたたみプラグインv2","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"relatedlist","関連ページの一覧プラグイン","https://wiki.dobon.net/index.php?PukiWiki%2F%BC%AB%BA%EE%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3%2Frelatedlist.inc.php")
.setSimplePluginHtml($loginid,"seimei","姓名判断プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/seimei.inc.php")
.setSimplePluginHtml($loginid,"slideshow","slick.jsを利用したスライドショーを表示するプラグイン","https://jpngamerswiki.com/?ff7d0a095a")
.setSimplePluginHtml($loginid,"splitbody","文章を分割し、マルチカラムで表示するプラグイン","http://pukiwiki.sonots.com/?Plugin/splitbody.inc.php")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"style","CSS スタイル指定プラグイン","http://pukiwiki.sonots.com/?Plugin%2Fstyle.inc.php")."※このプラグインを利用するには<strong>Wiki詳細設定</strong>から<strong>マルチラインプラグインハック</strong>を有効にする必要があります。<br />"
.setSimplePluginHtml($loginid,"sub","上付き文字プラグイン","https://pukiwiki.osdn.jp/dev/?PukiWiki/1.4/%E3%81%A1%E3%82%87%E3%81%A3%E3%81%A8%E4%BE%BF%E5%88%A9%E3%81%AB/%E4%B8%8A%E4%BB%98%E3%81%8D%E4%B8%8B%E4%BB%98%E3%81%8D%E6%96%87%E5%AD%97")
.setSimplePluginHtml($loginid,"sup","下付き文字プラグイン","https://pukiwiki.osdn.jp/dev/?PukiWiki/1.4/%E3%81%A1%E3%82%87%E3%81%A3%E3%81%A8%E4%BE%BF%E5%88%A9%E3%81%AB/%E4%B8%8A%E4%BB%98%E3%81%8D%E4%B8%8B%E4%BB%98%E3%81%8D%E6%96%87%E5%AD%97")
.setSimplePluginHtml($loginid,"tasks","task(todo)管理用プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/tasks.inc.php")
.setSimplePluginHtml($loginid,"tex","Google Chart API版数式プラグイン(不具合あり)","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/tex.inc.php")
.setSimplePluginHtml($loginid,"tex2","GoogleCharts版数式プラグイン","http://pkom.ml/?%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/tex2.inc.php")
.setSimplePluginHtml($loginid,"title","タイトル表示変更プラグイン","https://wiki.dobon.net/index.php?PukiWiki%2F%BC%AB%BA%EE%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3%2Ftitle.inc.php")
.setSimplePluginHtml($loginid,"todo","TODO など、各ページに散らばったマーク付き行を grep して一覧表示するプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/todo.inc.php")
.setSimplePluginHtml($loginid,"topicpath","topicpathプラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/topicpath.inc.php")
.setSimplePluginHtml($loginid,"twitter","Twitterのタイムラインや単一のツイートを埋め込んで表示するプラグイン","https://jpngamerswiki.com/?0f5ec903b8")
.setSimplePluginHtml($loginid,"ul","表組み内リスト表示用プラグイン","https://jpngamerswiki.com/?54760078c9")
.setSimplePluginHtml($loginid,"url","URLからタイトルを取得してリンクにするプラグイン","https://dexlab.net/pukiwiki/index.php?Software/wiki%BC%AB%BA%EE%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/url.inc.php")
.setSimplePluginHtml($loginid,"webdl","ディレクトリ内のファイルをダウンロードするプラグイン※秘密情報漏洩の危険性が疑われます。","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/webdl.inc.php")
.setSimplePluginHtml($loginid,"whatsnew","What's New!プラグイン","https://pukiwiki.osdn.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3/whatsnew.inc.php")
.setSimplePluginHtml($loginid,"youtube2","新YouTube埋め込みプラグイン","https://jpngamerswiki.com/?82f1460fdb")
; 
?>
                <input type="submit" name="wikicpplgsettings1save" id="wikicpplgsettings1save" value="適用"></input>
            </form>
        </div>
        <a href="javascript:if(wikipagemgr_hide == 0){document.getElementById('wikipagemgr1').style.display='none';document.getElementById('wikipagemgr2').style.display='inline-block';document.getElementById('wikipagemgropen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ページマネージャー&nbsp;</ele>[-]</h2>';wikipagemgr_hide=1;}else if(wikipagemgr_hide == 1){document.getElementById('wikipagemgr2').style.display='none';document.getElementById('wikipagemgr1').style.display='inline-block';document.getElementById('wikipagemgropen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ページマネージャー&nbsp;</ele>[+]</h2>';wikipagemgr_hide=0;}" id="wikipagemgropen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >ページマネージャー&nbsp;</ele>[+]</h2></a>
        <div id="wikipagemgr1" style="display: inline-block;"></div>
        <div id="wikipagemgr2" style="display: none;">
                ページ名をクリックするとコントロールパネルで編集できます。<br />
<?php
if ($wiki_fullpath=="dir"){
    $fordir = './'.$loginid.'/wiki';
    foreach (new DirectoryIterator($fordir) as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $filename = $fileinfo->getFilename();
            if (($filename != ".htaccess") && ($filename != "index.html")){
                echo "<a href=\"./?controlpanel&pageeditor=".$filename."\">".hex2bin(pathinfo($filename)['filename'])."</a> - <a href=\"./?controlpanel&pagedownloader=".$filename."\">[ダウンロード]</a> - <a href=\"./".$loginid."/?".hex2bin(pathinfo($filename)['filename'])."\">[Wikiのページへ移動]</a><br />";
            }
        }
    }
}else{
    echo urlSend("http://".$loginid.".wikic.ga/wikic/getfilelist.php",array('dir' => 'wiki','pass' => $urlsendpassword));
}
?>
        </div>
        <a href="javascript:if(wikifilemgr_hide == 0){document.getElementById('wikifilemgr1').style.display='none';document.getElementById('wikifilemgr2').style.display='inline-block';document.getElementById('wikifilemgropen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ファイルマネージャー&nbsp;</ele>[-]</h2>';wikifilemgr_hide=1;}else if(wikifilemgr_hide == 1){document.getElementById('wikifilemgr2').style.display='none';document.getElementById('wikifilemgr1').style.display='inline-block';document.getElementById('wikifilemgropen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>ファイルマネージャー&nbsp;</ele>[+]</h2>';wikifilemgr_hide=0;}" id="wikifilemgropen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >ファイルマネージャー&nbsp;</ele>[+]</h2></a>
        <div id="wikifilemgr1" style="display: inline-block;"></div>
        <div id="wikifilemgr2" style="display: none;">
        この機能はまだβ版であり、不具合が発生する場合がございます。
        <br />※将来的には機能が削除される可能性もあります。<br />
        <a href="./?controlpanel&filelist">ファイルマネージャー</a>
        </div>

        <a href="javascript:if(wikiupdate_hide == 0){document.getElementById('wikiupdate1').style.display='none';document.getElementById('wikiupdate2').style.display='inline-block';document.getElementById('wikiupdateopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>アップデート&nbsp;</ele>[-]</h2>';wikiupdate_hide=1;}else if(wikiupdate_hide == 1){document.getElementById('wikiupdate2').style.display='none';document.getElementById('wikiupdate1').style.display='inline-block';document.getElementById('wikiupdateopen').innerHTML='<h2><ele style=&quot;text-decoration:none;color:black;display:inline-block;&quot;>アップデート&nbsp;</ele>[+]</h2>';wikiupdate_hide=0;}" id="wikiupdateopen"><h2><ele style="text-decoration:none;color:black;display:inline-block;" >アップデート&nbsp;</ele>[+]</h2></a>
        <div id="wikiupdate1" style="display: inline-block;"></div>
        <div id="wikiupdate2" style="display: none;">
                このWikiは最新バージョンです。<br />
<?php

?>
        </div>
    </body>
<?php
    }
    }else{
?>
    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
        <title>ログイン - WikiC</title>
    </head>
    <body>
        <h2>コントロールパネルへログイン</h2>
        <form method="post">
            Wiki ID:<input type="text" name="loginid" id="loginid" placeholder="examplewiki"></input>
            <br />
            パスワード:<input type="password" name="password" id="password" placeholder="パスワード"></input>
            <br />
            <input type="submit" name="wikicplogin" id="wikicplogin" value="ログイン"></input>
        </form>
        <a href="./">トップページ</a>
    </body>
<?php }} ?>
</html>