<?php
function ksid()
{
    return md5(md5(uniqid('', true).uniqid('', true).uniqid('', true).rand(0, 9999999999).substr(md5('iugkfuhk'), 0, 10)).rand(0, 9999999999));
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
    $encryption_key　= "4b2650e514c7855253616ccbb5bbccd37465645a62f5f39931176b1d1af0d74f";
    $encrypted = openssl_encrypt($endata, $method, $encryption_key);
    return $encrypted;
}

function pass_decryption($dedata) {
    $method = 'aes-128-cbc';
    $encryption_key　= "4b2650e514c7855253616ccbb5bbccd37465645a62f5f39931176b1d1af0d74f";
    $decrypted = openssl_decrypt($dedata, $method, $encryption_key);
    return $decrypted;
}
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
        }
    }
}

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