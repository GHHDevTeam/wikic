<?php

/*------------------------------------------------------------
  メール送信フォームプラグイン

  Copyright 2004, Yoshihiro Kawamata, kaw@on.rim.or.jp
  $Id: mail.inc.php,v 1.19 2004/03/19 15:24:32 cvs Exp $

  ------------------------------------------------------------
  Usage: #mail(src,dest,cc,subject,body)
    src   ... 発信メールアドレス
    dest  ... 宛先メールアドレス
    cc    ... Carbon Copy (省略可)
    subject . 表題 (省略可)
    body  ... 本文 (省略可)

    cc, subject, bodyに関しては、先頭に以下の文字を置くことで、
    フォームの表示方法を選択できる。

        -foo : 値をfooに設定。(フォーム上は非表示)
        =foo : 値をfooに設定し、テキストとして表示(ユーザ変更不可)
        +foo : 値をfooに設定し、フォーム内に表示(ユーザ変更可)
        @foo : +fooとおなじだが、内容を添付ファイルfooからインクルードする
               (本文でのみ有効)

        foo  : 「=foo」と同じ
    空文字列 : 「+」として解釈(空のフォームを表示)。
        !    : そのフィールドは使用しない。

  ------------------------------------------------------------
  例: #mail(foo@bar.jp,bee@baz.jp,!,=Hogeについて,ここに本文を入れてね)

       → foo@bar.jpからbee@baz.jpへメールを送る。
           ・ Cc:は使用しない
           ・ 表題は、「Hogeについて」で固定。変更不可
           ・ 本文の入力フォームには、最初から「ここに本文を入れてね」と入力されている。

  ------------------------------------------------------------
  注意:
    ・ MIMEや漢字コード変換を行なっているので、PHPにはmbstring機能が
       組み込まれている必要がある。
    ・ デフォルトでは、匿名によるメール送信が悪用されるのを防ぐため、
       ページが凍結されていないと使用できない。 
  ------------------------------------------------------------
　2004／10／13 　表示内容について改造　by　ねこご
  改造点:
    ・ 各フィールドの記入のうち、送信元アドレスを「内緒」をデフォルトにし、
    　送信者が自由に書き換えられるようにした。
    ・ 各フィールドの記入のうち、送信先アドレスとCcを非表示とした。
    ・ 送信後の表示を送信元アドレス、件名、内容とした。
  ------------------------------------------------------------*/

#========================================
# User customizable parameters;
#
define('MAILFORM_CC_SIZE',   40);
define('MAILFORM_SUBJ_SIZE', 40);
define('MAILFORM_BODY_ROWS', 15);
define('MAILFORM_BODY_COLS', 70);
#
#========================================

define('MAILFORM_DEBUGGING', 0);
define('MAILFORM_CHECK_PAGE_FREEZED', 1);
define('MAILFORM_UPLOAD_DIR', './attach/');

function plugin_mail_nullstr($s) {
  return($s == '' ? '(なし)' : $s);
}

function plugin_mail_action() {
  global $post;

  if ( $post['cc'].$post['subj'].$post['body'] == "" ) {
    return(array('msg'  => 'Mail not sent',
                 'body' => '内容が空なので、メールは送信されませんでした。'));
  }

  mb_internal_encoding('EUC-JP');

  $opthdr = "From: ".$post['from']
          . "\nContent-Type: Text/Plain; charset=ISO-2022-JP";

  if ( $post['cc'] != '') {
    $opthdr .= "\nCc: ".$post['cc'];
  }

  if (!MAILFORM_DEBUGGING) {
    mail($post['to'],
         mb_encode_mimeheader($post['subj'], 'JIS', 'B'),
         mb_convert_encoding($post['body'], 'JIS'),
         $opthdr);
  }

  $retbody  = "メールを送信しました。送られた内容は以下のとおりです。<br /><br />\n"


             ."メール:<pre>\n".htmlspecialchars($post['from'])."\n</pre>"
             ."件　名:<pre>\n".htmlspecialchars($post['subj'])."\n</pre>"
             ."内　容:<pre>\n".htmlspecialchars($post['body'])."\n</pre>";
  return(array('msg' => 'Mail sent', 'body' => $retbody));
}

define('MAILFORM_INPUT_TEMPL',   '<input type="%s" name="%s" value="%s"%s />');
define('MAILFORM_TXTAREA_TEMPL', '<textarea name="%s"%s>%s</textarea>');

function plugin_mail_outtag() {
  list($in, $name, $tagtype, $opts) = func_get_args();

  /*
     与えられた書式にしたがってFormタグの文字列を生成し、返す。

     引数 :
       $in : タグのvalue属性を指定、以下の書式に従う；
         -foo : hiddenタグとしてfooを出力。
         =foo : hiddenタグとしてfooを出力しつつfooの値もスタティックに表示する。
         +foo : inputタグあるいはtextareaタグとしてfooを出力。
                fooはタグの初期値として設定される。
         @foo : +fooとおなじだが、内容を添付ファイルfooからインクルードする。

         foo  : 「=foo」として解釈。
         ""   : 「+」として解釈。
         "!"  : 空文字列を返す。

       $name : タグのname属性を指定
       $tagtype : タグのtype、'textarea'でTEXTAREAタグ、それ以外ではinputタグを生成。
       $opts : その他の属性を指定したい場合に設定。

     返値  ...  list($type, $value) = plugin_mail_outtag()
       $type : タグの形式を表す1文字。意味は入力$inの書式と同じ。
       $value : 生成されたタグ文字列
 */

  global $vars;

  if ($opts != '') { $opts = ' '.$opts; }

  if ($in == '') {
    $rettype = '+';
    if ($tagtype == 'textarea') {
      $retval = sprintf(MAILFORM_TXTAREA_TEMPL, $name, $opts, '');
    } else {
      $retval = sprintf(MAILFORM_INPUT_TEMPL, 'text', $name, '', $opts);
    }
  } else {
    # first character ... may be directive
    $fchar = htmlspecialchars(substr($in, 0, 1));

    # rest string ... data
    $rstr  = htmlspecialchars(substr($in, 1));

    $in = htmlspecialchars($in);

    $rettype = $fchar;
    switch ($fchar) {
      case '-': $retval = sprintf(MAILFORM_INPUT_TEMPL, 'hidden', $name, $rstr, '');
                break;
      case '=': $retval = sprintf(MAILFORM_INPUT_TEMPL, 'hidden', $name, $rstr, '').$rstr;
                break;
      case '@': /* get the text from attached file */
                $fpath = MAILFORM_UPLOAD_DIR.encode($vars[page]).'_'.encode($rstr);
                if (!is_file($fpath) || !is_readable($fpath)) {
                  $rstr = "(インクルードファイル${rstr}が見つかりません)";
                } else {
                  $rstr = file_get_contents($fpath);
                  $rstr = mb_convert_encoding($rstr, 'EUC-JP',
                                              mb_detect_encoding($rstr, 'SJIS,EUC-JP,JIS'));
                }
        /* ---- NO BREAK  ----
           ---- FALL THRU ---- */
      case '+': if ($tagtype == 'textarea') {
                  $retval = sprintf(MAILFORM_TXTAREA_TEMPL, $name, $opts, $rstr);
                } else {
                  $retval = sprintf(MAILFORM_INPUT_TEMPL, 'text', $name, $rstr, $opts);
                }
                break;
      case '!': $retval = '';
                break;
      default:  $rettype = '+';
                if ($tagtype == 'textarea') {
                  $retval = sprintf(MAILFORM_TXTAREA_TEMPL, $name, $opts, $in);
                } else {
                  $retval = sprintf(MAILFORM_INPUT_TEMPL, 'text', $name, $in, $opts);
                }

    }
  }
  return array($rettype, $retval);
}

function plugin_mail_convert() {
  global $script, $vars;

  if (!MAILFORM_DEBUGGING
      && MAILFORM_CHECK_PAGE_FREEZED
      && !is_freeze($vars['page'])) {
    return 'このページは凍結されていないので、mailプラグインは使用できません。';
  }

  list($from, $to, $cc, $subj, $body) = func_get_args();

  if ( $from == "" ) {
    $from ="内緒";
  }
  if ( $to == "" ) {
    return('宛先が指定されていません。Mailプラグインの記述方法を確認して下さい。');
  }

  $retstr  = '<form action="'.$script.'?plugin=mail" method="post">'."\n";
  $retstr .= '<table border="0">'."\n";

  #
  # 各フィールドや本文の記入欄を生成
  #

  # From:
  list($t, $v) = plugin_mail_outtag($from, 'from', '', 'size="'.MAILFORM_CC_SIZE.'"');
  if ($t == '-') {
    $retstr .= $v."\n";
  } elseif (($t != '!') && ($t != '@')) {
    $retstr .= '<tr><td style="text-align: right" nowrap>メール:</td><td>'.$v.'</td></tr>'."\n";
  }

  # To:
  list($t, $v) = plugin_mail_outtag("-$to", 'to');
 $retstr .= $v."\n";

  # Cc:
  list($t, $v) = plugin_mail_outtag("-$cc", 'cc');
    $retstr .= $v."\n";

  # Subject:
  list($t, $v) = plugin_mail_outtag($subj, 'subj', '', 'size="'.MAILFORM_SUBJ_SIZE.'"');
  if ($t == '-') {
    $retstr .= $v."\n";
  } elseif (($t != '!') && ($t != '@')) {
    $retstr .= '<tr><td style="text-align: right" nowrap>件　名:</td><td>'.$v.'</td></tr>'."\n";
  }

  # Body
  list($t, $v) = plugin_mail_outtag($body, 'body', 'textarea',
                                    'rows="'.MAILFORM_BODY_ROWS.'" cols="'.MAILFORM_BODY_COLS.'"');
  if ($t == '-') {
    $retstr .= $v."\n";
  } elseif ($t == '@') {
    $retstr .= '<tr><td style="text-align: right; vertical-align: top" nowrap>内　容:</td><td>'.$v.'</td></tr>'."\n";
  } elseif ($t != '!') {
    $retstr .= '<tr><td style="text-align: right; vertical-align: top" nowrap>内　容:</td><td>'.$v.'</td></tr>'."\n";
  }

  $retstr .= "<tr>\n"
           . "<td></td>\n"
           . "<td>\n"
           . "<input type=\"submit\" value=\"送信\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
           . "<input type=\"reset\" value=\"ご破算\" />\n"
           . "</td>\n"
           . "</tr>\n"
           . "</table>\n"
           . "</form>\n";

  return($retstr);
}

?>
                                         7   7   n                                                                                                                                                                                                                                                   	 Osaka          2 ・・2 ・・    YooEdit     7   7   nSORT*   n EFNT   "ETAB   .STR    :WPos   F譟    撻・・   
撻反・   +撻・   撻                                                                                           