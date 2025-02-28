<?php

function plugin_create_wiki_convert(){
	include_once("../api/api.php");
	$error = "";
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
                $file_handler = fopen('../account.txt', r);
                while ($data = fgets($file_handler)) {
                    if (preg_match('/\''.$_POST['dir'].'\'/', $data)) {
                        $user_dup = 'true';
                    }
                }
                fclose($file_handler);
                if ($user_dup == 'false') {
                    $file_handlea = fopen('../account.txt', a);
                    fwrite($file_handlea, "'".$encryptedpassword."','".$_POST['username']."','".$_POST['dir']."','".$_POST['title']."','".$_POST['type']."','".$encryptedip."','".ksid()."','".$encryptedpassword."','"."1"."','"."1"."','".$url."','"."1"."'\n");//17 break 19 nowikiname
                    fclose($file_handlea);
                    $error .= '<h3>正常にアカウントを登録しました。</h3>';
                    $zip = new ZipArchive();
                    if (file_exists('../../'.$_POST['dir'].'/')) {
                        $error .= '<h3>既にそのWiki IDは使われています。</h3>';
                    } else {
                        if ($_POST['type'] == 'pukiwiki') {
                            $wiki_zipfile = 'pukiwiki.zip';
                        }
                        if ($_POST['type'] == 'dokuwiki') {
                            $wiki_zipfile = 'dokuwiki.zip';
                        }
                        if ($zip->open('../../'.$wiki_zipfile) == true) {
                            $zip->extractTo('../../'.$_POST['dir'].'/');
                            $zip->close();
                            if ($_POST['type'] == 'pukiwiki') {
                                $filedataurl = file_get_contents("../../".$_POST['dir']."/pukiwiki.ini.php");
                                $filedata = $filedataurl;
                                $filedata = str_replace('$modifier = \'anonymous\';', '$modifier = \''.$_POST['username'].'\';', $filedata);
                                $filedata = str_replace('$modifierlink = \'http://pukiwiki.example.com/\';', '$modifierlink = \''.$url.'\';', $filedata);
                                $filedata = str_replace('$page_title = \'PukiWiki\';', '$page_title = \''.$_POST['title'].'\';', $filedata);
                                $filedata = str_replace('$adminpass = \'{x-php-md5}!\';', '$adminpass = \'{x-php-md5}\' . md5(\''.$_POST['password'].'\');', $filedata);
                                $filedata = str_replace('$line_break = 0;', '$line_break = 1;', $filedata);
                                $filedata = str_replace('$nowikiname = 0;', '$nowikiname = 1;', $filedata);
                                file_put_contents('../../'.$_POST['dir'].'/pukiwiki.ini.php', $filedata);
                                $error .= '<h3>Wikiが生成されました→<a href="'.$url.$_POST['dir'].'/">'.$url.$_POST['dir'].'/</a></h3>';
                            }
                            if ($_POST['type'] == 'dokuwiki') {
                                $error .= '<h3>Wikiが生成されました。セットアップ画面→<a href="'.$url.$_POST['dir'].'/install.php">'.$url.$_POST['dir'].'/install.php</a></h3>';
                            }
                        } else {
                            $error .= '<h3>失敗しました。再度お試しください。</h3>';
                        }
                    }
                }
            } else {
                $error .= '<h3>エラー:パスワードが入力されていません。</h3>';
            }
        } else {
            $error .= '<h3>エラー:Wiki IDが入力されていません。</h3>';
        }
    }
    return $error . '<form method="post" action="./?create">
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
        </form>';
}
