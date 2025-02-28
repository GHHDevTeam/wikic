<?php
/*
 * 色んなものを一括フリーズするプラグイン
 * $Id: freeze2.inc.php 149 2005-03-30 16:09:12Z okkez $
 *
 * @version 1.12
 * @remodeler K
 * ライセンス
 * GPL
 */
include_once(PLUGIN_DIR.'attach.inc.php');
include_once(PLUGIN_DIR.'freeze.inc.php');
include_once(PLUGIN_DIR.'deldel.inc.php');

function plugin_freeze2_action()
{
    global $_attach_messages,$_freeze2_messages;
    global $vars,$script;
    //messages
	$_freeze2_messages = array(
    'title_freeze2' => '複数ページ一括凍結プラグイン',
    'title_list' => 'ページの一覧',
    'title_attachlist' => '添付ファイルの一覧',
    'title_freeze_page' => 'ページを一括凍結しました',
    'title_freeze_attach' => '添付ファイルを一括凍結しました',
    'title_freeze_error' => 'エラー',
    'title_select_list' => '選択された一覧',
    'msg_error' => 'ちゃんと凍結するページを選んで下さい！',
    'msg_body_start' => '操作したいデータを選んで、管理者パスワードを入力して検索ボタンを押して下さい。',
    'msg_check' => '凍結したいものにチェックを入れて確認ボタンを押して下さい。',
    'msg_auth' => 'これらのファイルを凍結してよければ、凍結用パスワードを入力して凍結ボタンを押して下さい。',
    'msg_page' => '複数のページを一括凍結しました。',
    'msg_auth_error' => '管理者パスワードが一致しません。',
    'msg_freeze_error' => '凍結しようとしたファイルはもう既にないか、何らかの理由で凍結できませんでした。確認して下さい。',
    'msg_freeze_success' => '以上のファイルを凍結しました。',
    'msg_fatal_error' => '何か変です！何が変かはわかりません。',
    'msg_back_word' => '戻る',
    'msg_regexp_label' => 'パターン：',
    'msg_regexp_error' => 'そんなパターンを含むページありません！',
    'btn_exec' => '凍結',
    'btn_search' => '検索',
    'btn_concern' => '確認');
    //ここまで
    //変数の初期化
    $mode = isset($vars['mode']) ? $vars['mode'] : NULL;
    $status = array(0 => $_freeze2_messages['title_freeze_error'],
                    1 => $_freeze2_messages['btn_freeze']);
    if(!isset($mode)){
        //最初のページ
        $body  = "<form method='post' action=\"$script?cmd=freeze2\"><div>";
        $body .= '<select name="dir" size="1">';
        $body .= '<option value="DATA">wiki</option>';
        $body .= '<option value="UPLOAD">attach</option></select></div>';
        $body .= "<div><input type=\"password\" name=\"pass\" size=\"12\"/>\n";
        $body .= "<input type=\"hidden\" name=\"mode\" value=\"select\"/>\n";
        $body .= "<input type=\"submit\" value=\"".$_freeze2_messages['btn_search']."\" /></div></form>";
        $body .= "<p>".$_freeze2_messages['msg_body_start']."</p>";
        return array('msg'=>$_freeze2_messages['title_freeze2'],'body'=>$body);
    }elseif(isset($mode) && $mode === 'select'){
        if(isset($vars['pass']) && pkwk_login($vars['pass'])) {
            //認証が通ったらそれぞれページ名やファイル名の一覧を表示する
            $vars['pass'] = '';//認証が終わったのでパスを消去
            if(isset($vars['dir']) && $vars['dir']==="DATA") {
                //ページ
                $body .= make_body($vars['cmd'], DATA_DIR);
                return array('msg'=>$_freeze2_messages['title_list'],'body'=>$body);
            }
            elseif(isset($vars['dir']) && $vars['dir']==="UPLOAD"){
                //添付ファイル
                $body = "\n<form method=\"post\" action=\"$script?cmd=freeze2\"><div>";
                $retval = attach_list2();
                $body .= $retval['body'];
                $body .= "<input type=\"hidden\" name=\"mode\" value=\"confirm\"/>\n<input type=\"hidden\" name=\"dir\" value=\"".$vars['dir']."\"/>\n";
                $body .= "<input type=\"submit\" value=\"".$_freeze2_messages['btn_concern']."\"/></div>\n</form>";
                $body .= $_freeze2_messages['msg_check'];
                return array('msg'=>$retval['msg'],'body'=>$body);
            }
        }
        elseif(isset($vars['pass']) && !pkwk_login($vars['pass'])){
            //認証エラー
            return array('msg' => $_freeze2_messages['title_freeze_error'],'body'=>$_freeze2_messages['msg_auth_error']);
        }
    }elseif(isset($mode) && $mode === 'confirm'){
        //確認画面+もう一回認証要求？
        if(array_key_exists('page',$vars) and $vars['page'] != ''){
            return make_confirm('freeze2', $vars['dir'], $vars['page']);
        }elseif(array_key_exists('regexp',$vars) && $vars['regexp'] != ''){
            $pattern = $vars['regexp'];
            foreach ( get_existpages() as $page ) {
                if (mb_ereg($pattern, $page)) {
                    $target[] = $page;
                }
            }
            if(is_null($target)){
                $error_msg = "<p>".$_freeze2_messages['msg_regexp_error']."</p>\n";
                $error_msg .= "<p>". htmlsc($vars['regexp']) ."</p>";
                $error_msg .= "<p><a href=\"$script?cmd=freeze2\">".$_freeze2_messages['msg_back_word']."</a></p>";
                return array('msg'=>$_freeze2_messages['title_freeze_error'] ,'body'=>$error_msg);
            }
            return make_confirm('freeze2', $vars['dir'], $target);
        }else{
            //選択がなければエラーメッセージを表示する
            $error_msg = "<p>".$_freeze2_messages['msg_error']."</p><p><a href=\"$script?cmd=freeze2\">".$_freeze2_messages['msg_back_word']."</a></p>";
            return array('msg'=>$_freeze2_messages['title_freeze_error'] ,'body'=>$error_msg);
        }
    }elseif(isset($mode) && $mode === 'exec'){
        //凍結
        if(isset($vars['pass']) && pkwk_login($vars['pass'])) {
            switch($vars['dir']){
              case 'DATA':
                $mes = 'page';
                foreach($vars['page'] as $page) {
                    $s_page = htmlsc($page, ENT_QUOTES);
                    if(is_page($s_page) && !is_freeze($s_page)){
                        $flag[$s_page] = true;
                        $postdata = get_source($page);
                        array_unshift($postdata, "#freeze\n");
                        $postdata = join('', $postdata);
                        file_write(DATA_DIR,$page, $postdata, TRUE);
                    }else{
                        $flag[$s_page] = false;
                    }
                }
                break;
              case 'UPLOAD':
                $mes = 'attach';
                $size = count($vars['file_a']);
                for($i=0;$i<$size;$i++){
                    foreach (array('refer', 'file', 'age') as $var) {
                        $vars[$var] = isset($vars[$var.'_a'][$i]) ? $vars[$var.'_a'][$i] : '';
                    }
                    $result = attach_freeze(TRUE);
                    //それぞれのファイルについて成功|失敗のフラグを立てる
                    switch($result['msg']){
                      case $_attach_messages['msg_freezed']:
                        $flag[$vars['refer']."/".$vars['file']] = true;
                        break;
                      case $_attach_messages['err_notfound'] || $_attach_messages['err_noparm']:
                        $flag[$vars['refer']."/".$vars['file']] = false;
                        break;
                      default:
                        $flag[$vars['refer']."/".$vars['file']] = false;
                        break;
                    }
                }
                break;
            }
            if(in_array(false,$flag)){
                //凍結失敗したものが一つでもある
                foreach($flag as $key=>$value){
                    $body .= "$key =&gt; ".$status[$value]."<br/>\n";
                }
                $body .= "<p>".$_freeze2_messages['msg_freeze_error']."</p>";
                return array('msg' => $_freeze2_messages['title_freeze_error'],'body'=>$body);
            }else{
                //凍結成功
                foreach($flag as $key=>$value){
                    $body .= "$key<br/>\n";
                }
                $body .= "<p>".$_freeze2_messages['msg_freeze_success']."</p>";
                return array('msg' => $_freeze2_messages['title_freeze_'.$mes] ,'body' => $body);
            }
        }
        elseif(isset($vars['pass']) && !pkwk_login($vars['pass'])){
            //認証エラー
            return array('msg' => $_freeze2_messages['title_freeze_error'],'body'=>$_freeze2_messages['msg_auth_error']);
        }
    }
}
?>