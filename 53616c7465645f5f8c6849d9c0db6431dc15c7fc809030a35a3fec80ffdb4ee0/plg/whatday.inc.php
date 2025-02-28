<?php
// $Id: whatday.inc.php,v 1.0 2003/04/20 00:00:00 upk Exp $
/*
 * PukiWiki whatday プラグイン (What Day)
 * (C) 2003, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * License: GPL
 *
 * メールマガジン「何の日Anniversary」 http://nnh.pos.to/mm/
 * 提供データから、今日は何の日を表示する。
 *
 */

// http://nnh.pos.to/mm/
define('WHATDAY_LINK','http://nnh.pos.to/mm/');
// See: http://nnh.pos.to/mm/history.csv
define('WHATDAY_HISTORY','history.csv'); // 歴史データベース
// See: http://nnh.pos.to/mm/person.csv
define('WHATDAY_PERSON','person.csv');   // 誕生日・忌日データベース

function plugin_whatday_init()
{
  switch (LANG) {
    case 'ja':
      $msg = whatday_init_ja();
      continue;
    default:
      $msg = whatday_init_en();
  }
  set_plugin_messages($msg);
}

function whatday_init_ja()
{
  $msg = array(
    '_whatday_msg' => array(
      'msg_H3_Histry'        => '歴史上の出来事',
      'msg_H3_Person'        => '今日の誕生日',
      'msg_InformationOffer' => '情報提供',
      'msg_MailMagazine'     => 'メールマガジン',
      'msg_ML_Name'          => '「何の日Anniversary」',
      'msg_None'             => '特記事項なし',
      'msg_Unknown'          => '&nbsp;不&nbsp;&nbsp;明&nbsp;',
      'msg_Year'             => '年 ',
      'msg_Title'            => ' さん ',
    )
  );
  return $msg;
}

function whatday_init_en()
{
  $msg = array(
    '_whatday_msg' => array(
      'msg_H3_Histry'        => 'A historical occurrence',
      'msg_H3_Person'        => 'Today\'s birthday',
      'msg_InformationOffer' => 'Information Offer',
      'msg_MailMagazine'     => 'Mail magazine',
      'msg_ML_Name'          => '[What day Anniversary]',
      'msg_None'             => 'With no special mention matter',
      'msg_Unknown'          => 'Unknown',
      'msg_Year'             => '',
      'msg_Title'            => '',
    )
  );
  return $msg;
}

function plugin_whatday_convert() {

  if (defined("ZONETIME")) {
    $utime = UTIME + ZONETIME;
  } else {
    $utime = UTIME;
  }

  list($m,$d,$flag) = func_get_args();

  if (is_null($m) || empty($m)) {
    $m = date("m", $utime);
  }
  if (is_null($d) || empty($d)) {
    $d = date("d", $utime);
  }
  if (is_null($flag) || empty($flag)) {
    $flag = "hb";
  }

  return whatday_message($m,$d,$flag);
}

// 今日のメッセージ
function whatday_message($m,$d,$flag) {
  global $_whatday_msg;

  $msg = "";

  /* ************ */
  /* *  出来事  * */
  /* ************ */
  if (strchr($flag,"h")) {
    $rc = td_fgetcsv(WHATDAY_HISTORY,1,$m,$d);
    if (is_array($rc)) {
      $msg .= "<h3>".$_whatday_msg['msg_H3_Histry']." (".
        $_whatday_msg['msg_InformationOffer'].": ".
        "<a href=\"".WHATDAY_LINK."\">".
        $_whatday_msg['msg_MailMagazine'].
        $_whatday_msg['msg_ML_Name']."</a>)".
        "</h3><br />\n";
      $i = 0;
      foreach($rc as $x) {
        $i++;
        $msg .= "<div>".$x[0].$_whatday_msg['msg_Year'].$x[2]."</div>\n";
      }
      if ($i == 0)
        $msg .= "<div>".$_whatday_msg['msg_None']."</div>\n";
      $msg .= "<br />";
    }
  }

  /* ************ */
  /* *  誕生日  * */
  /* ************ */
  if (strchr($flag,"b")) {
    $rc = td_fgetcsv(WHATDAY_PERSON,2,$m,$d);
    if (is_array($rc)) {
      $msg .= "<h3>".$_whatday_msg['msg_H3_Person']." (".
        $_whatday_msg['msg_InformationOffer'].": ".
        "<a href=\"".WHATDAY_LINK."\">".
        $_whatday_msg['msg_MailMagazine'].
        $_whatday_msg['msg_ML_Name']."</a>)".
        "</h3><br />\n";
      foreach($rc as $x) {
        $msg .= "<div>";
        if (empty($x[1]))
          $msg .= $_whatday_msg['msg_Unknown'];
        else
          $msg .= $x[1].$_whatday_msg['msg_Year'];
        $msg .= $x[0].$_whatday_msg['msg_Title']." (".$x[8].")</div>\n";
      }
      $msg .= "<br />";
    }
  }
  return $msg;
}

// メールマガジン「何の日Anniversary」 http://nnh.pos.to/mm/
// 提供データから、CSVファイル中の月日項目と一致するデータの取得
function td_fgetcsv($file,$ChkOffset,$m,$d) {
  $rc    = array();
  $ctr   = 0;
  $ChkMd = $m*100 + $d;

  $fp = @fopen ($file,"r");
  while ($data = @fgetcsv($fp, 1000, ",")) {
    if (empty($data[$ChkOffset])) continue;
    if ($ChkMd == $data[$ChkOffset]) {
      $rc[$ctr++] = $data;
    }
  }
  fclose ($fp);
  return $rc;
}

?>
