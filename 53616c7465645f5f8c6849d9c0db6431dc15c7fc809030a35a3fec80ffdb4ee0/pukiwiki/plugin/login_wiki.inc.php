<?php

function plugin_login_wiki_convert(){
	include_once("../api/api.php");
	$error = "";
	session_start();
	if (isset($_SESSION['cwiki_loginid'])){
		header("location: ./controlpanel.php");
		exit;
	}
    if (isset($_POST['wikicplogin'])){
        if ($_POST["loginid"] != null){
            if ($_POST["password"] != null){
                $data = null;
                $Logged_in_successfully = "false";
                $split_data = null;
                $file_handler = fopen("../account.txt",r);
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
                    header("location: ./controlpanel.php");
                    exit;
                }else{$error .= "<h3>エラー:Wiki IDまたはパスワードが間違っている可能性があります。</h3>";}
            }else{$error .= "<h3>エラー:パスワードが入力されていません。</h3>";}
        }else{$error .= "<h3>エラー:Wiki IDが入力されていません。</h3>";}
    }
    return '<h2>コントロールパネルへログイン</h2>'.$error.'
        <form method="post">
            Wiki ID:<input type="text" name="loginid" id="loginid" placeholder="examplewiki"></input>
            <br />
            パスワード:<input type="password" name="password" id="password" placeholder="パスワード"></input>
            <br />
            <input type="submit" name="wikicplogin" id="wikicplogin" value="ログイン"></input>
        </form>';
}
