<?php
//
// $Id:gyo2cal.inc.php,v 1.2 2007/08/07
// Copyright(C) sfuji 2005-2006 All Rights Reseved.
//
//履歴
// v1.0 新規レリース　2005.12.27
// v1.1 引数で年月指定、および　日本語/英語の指定が可  2006.01.20
// v1.2 秋分の日と敬老の日に挟まれた平日・土曜日は国民の休日(2007年から施行。
//-------------------------------
function plugin_gyo2cal_convert()
{
//-------------------------------
//... 設定 .............
  $DispLang = "";	 //表示の言語　jp:日本語　en:英語
/*			   横サイズとFontサイズ、500と8 440と6など。　*/
  $TwoGyoWidth = "500";  //横サイズpx	defaultはnull
  $TwoGyoSize  = "8";    //Fontサイズpt	defaultはnull
//... 設定はここまで ...
//
//過去、未来のカレンダ指定チェック 2006/01/20 ver1.1
$timeyymmdd = time();
if (func_num_args() > 0) {
  $array  = func_get_args();
  $SelectDate = $array[0] . ' 09:00:00';
  $timeyymmdd = strtotime($SelectDate);
  if ($array[1]) {$DispLang = $array[1];}
}
// 2006/01/20 ver1.1ここまで追記

  $jpYobi = array('(日曜日)','(月曜日)','(火曜日)','(水曜日)','(木曜日)','(金曜日)','(土曜日)');
  $ret = '' ;
  $CalCreatePrepare = CalCreatePrepare($timeyymmdd) ; //2006.10.20 ver1.1
  //$CalCreatePrepare = CalCreatePrepare(time()) ;
  $ret .= str_makecalendar($CalCreatePrepare,$jpYobi,$DispLang,$TwoGyoWidth,$TwoGyoSize);
  return $ret ;
}
//------------------------------
function CalCreatePrepare($time)
{
  $today = date("j", $time) ;
  $timeofday = 60 * 60 * 24 ;
  $time_1st = $time - ( ($today - 1) * $timeofday ) ;
  $time_end = date("t", $time_1st) ;
  $CalCreatePrepare[0]['week'] = date("w", $time_1st) ;
  $CalCreatePrepare[0]['time_end'] = $time_end ;
  $CalCreatePrepare[0]['fullname'] = date("F", $time_1st) ;
  $CalCreatePrepare[0]['yy'] = date("Y", $time_1st) ;
  $CalCreatePrepare[0]['mm'] = date("n", $time_1st) ;
  
  for($d = 1; $d <= $time_end; $d++)
  {
    $CalCreatePrepare[$d]['week'] = date("w", $time_1st + ($timeofday * ($d - 1) ) ) ;
  }
  return $CalCreatePrepare ;
}

//------------------------------
//カレンダーを作成。
function str_makecalendar($CalCreatePrepare,$jpYobi,$DispLang,$TwoGyoWidth,$TwoGyoSize)
{
  $olddate = FALSE;		//2006/01/20 ver1.1
  if (($CalCreatePrepare[0]['yy'] != date(Y)) || ($CalCreatePrepare[0]['mm'] != date(n))) $olddate = TRUE;	//2006/01/20 ver1.1
  $ret = "" ;
//ここからtableタグを作成
  if (!$olddate){		//2006/01/20 ver1.1
     if ($DispLang == "en"){
       $NowTime = date ("F　　") .'Today：' . date ("Y/n/j(l)");
     } else {
       $NowTime = date ("n月") . 'のカレンダー　　きょうは' . date ("Y/n/j") . $jpYobi[date(w)] . 'です';
     }
  } else {			//2006/01/20 ver1.1  以下を追記
     if ($DispLang == "en"){
       $NowTime = $CalCreatePrepare[0]['yy'].'/'.$CalCreatePrepare[0]['fullname'].'　Calendar';
     } else {
       $NowTime = $CalCreatePrepare[0]['yy'].'年'.$CalCreatePrepare[0]['mm'] . '月のカレンダー';
     }
  }				//2006/01/20 ver1.1


  $week = $prnweek = 0 ;
  $ret .= '<table style="table-layout: fixed; width: ' . $TwoGyoWidth . 'px; border: 0px solid #000000 ;padding : 2px ;">' . "\n" ;
  $ret .= '  <tr><td colspan="21" style="text-align:center;">' . $NowTime . '</td></tr>' . "\n" ;
  $ret .= '  <tr>';
for ($i=0; $i<3; $i++){
  if ($DispLang == "en"){
/*
     $ret .= '  <td class="2gyo_tdp"><font color="red">Su</font></td><td class="2gyo_td">Mo</td><td class="2gyo_td">Tu</td><td class="2gyo_td">We</td><td class="2gyo_td">Th</td><td class="2gyo_td">Fr</td><td class="2gyo_tdb"><font color="blue">Sa</font></td>' . "\n" ;
*/
     $ret .= '  <td  style="background : pink; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;"><font color="red">Su</font></td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">Mo</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">Tu</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">We</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">Th</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">Fr</td><td  style="background : lightblue; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">Sa</font></td>' . "\n" ;
  } else {
     $ret .= '  <td  style="background : pink; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;"><font color="red">日</font></td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">月</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">火</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">水</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">木</td><td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">金</td><td  style="background : lightblue; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">土</font></td>' . "\n" ;
  }
}
  $ret .= '  </tr>';
  while($week < $CalCreatePrepare[0]['week'])
  {
    if($week == 0)
    {
      $ret .= '  <tr>' ;
    }
    $ret .= '<td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">・</td>' ;
    $week++ ;
  }
  $trInsertCheck =0;
  for($d = 1; $d <= $CalCreatePrepare[0]['time_end'] ; $d++)
  {
    if($CalCreatePrepare[$d]['week'] == 0)
    {
      $trInsertCheck++;
      if ($trInsertCheck > 2) {
      $ret .= '  <tr>' ;$trInsertCheck = 0;}
    }
    if (($d == date(d) ) && (!$olddate)) {	//2006/01/20 ver1.1
      $ret .= '<td  style="background : yellow; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;"' ;
      $ret .= '>' ;
    } else {
      $ret .= '<td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;"' ;
      $ret .= '>' ;
    }
    $youbi = ($d + $week -1) % 7;
    if (isHoliday( $CalCreatePrepare[0]['yy'], $CalCreatePrepare[0]['mm'], $d )) {
      $ret .= '<font color="red">' . $d . '</font>';}
    elseif (isSaturday( $CalCreatePrepare[0]['yy'], $CalCreatePrepare[0]['mm'], $d )) {
      $ret .= '<font color="blue">' . $d . '</font>';}
    else {
      $ret .= $d ;
    }
    $ret .= '</td>';
    if($CalCreatePrepare[$d]['week'] == 6) $prnweek++;
    if((($CalCreatePrepare[$d]['week'] == 6) && ($CalCreatePrepare[0]['week'] == 0) && ($prnweek ==2)) || (($CalCreatePrepare[$d]['week'] == 6) && ($CalCreatePrepare[0]['week'] != 0) && ($prnweek ==3)))
    {
      $ret .= '</tr>' . "\n" ;
    }
  }
  $week = $CalCreatePrepare[( $CalCreatePrepare[0]['time_end'] )]['week'] + 1 ;

  while($week < 7)
  {
    $ret .= '<td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">・</td>' ;
    if($week == 6)
    {
      $trInsertCheck++;
    }
    $week++ ;
  }
if ($trInsertCheck < 3) {
for ($i=0; $i < 7; $i++) {
    $ret .= '<td  style="background : #ffffff; border: 1px solid #999999  ;text-align : center ;font-size: ' . $TwoGyoSize . 'pt;">・</td>' ;
}
$trInsertCheck++;
}
$ret .= '</tr>';
$ret .= '</table>' . "\n" ;
return $ret ;
}
//------------------
//土曜日チェック
function isSaturday( $year, $month, $day ) {
if( date("w", mktime(0,0,0, $month ,$day ,$year )) == 6 )
return TRUE;
}
//------------------
//日曜、祭日チェック
function isHoliday( $year, $month, $day ) {
if( date("w", mktime(0,0,0, $month ,$day ,$year )) == 0 )
return TRUE;		// 日曜であれば無条件に休日
$NationalHoliday = array("1/1","2/11","4/29", "5/3","5/4", "5/5", "11/3", "11/23", "12/23"); 	//固定された休日
if( $year > 1999 ) {	//春分秋分の日
$y = $year - 2000;
$spring_equinox   = (int)(20.69115 + 0.2421904 * $y - (int)($y/4 + $y/100 + $y/400) );
$autumnal_equinox = (int)(23.09000 + 0.2421904 * $y - (int)($y/4 + $y/100 + $y/400) );
array_push( $NationalHoliday, "3/".$spring_equinox );
array_push( $NationalHoliday, "9/".$autumnal_equinox );
}
if( array_search( $month."/".$day , $NationalHoliday ) !== FALSE )
return TRUE;		//固定休日及び春分秋分の日
$PreviousDay = $day - 1;//1日前を調べる
if( $PreviousDay < 1 )  // 無条件に休日でない日
return FALSE;
//振り替え休日
if( date("w", mktime(0,0,0, $month ,$PreviousDay ,$year ) ) == 0 ) {
  if( array_search( $month."/".$PreviousDay , $NationalHoliday ) !== FALSE )
  return TRUE;
}
//月曜日の祝日、月の第1月曜日を求める
for( $FirstMondays = 1; $FirstMondays < 8; $FirstMondays++ ) {
  if( date("w", mktime(0,0,0, $month ,$FirstMondays ,$year )) == 1 )
  break;
}
//第2月曜 (成人の日:1月の第2月曜日 / 体育の日:10月の第2月曜日)
if( ($month == 10) || ($month == 1) ) {
  if( $day == ($FirstMondays+7) )
  return TRUE;
}
//第3月曜（海の日:7月の第3月曜日 / 敬老の日:9月の第3月曜日)
if( ($month == 7) || ($month == 9) ) {
  if( $day == ($FirstMondays+14) )
  return TRUE;
}
// 秋分の日と敬老の日に挟まれた平日・土曜日は国民の休日(2007年から施行。2009年に現実に)
if ($month == 9){
    if (($day == ($FirstMondays + 15)) && (($day+1) == $autumnal_equinox))  return TRUE; // 月曜日が敬老の日で、水曜日が秋分の日であるときの火曜日
    if (($day == ($FirstMondays + 12)) && (($day-1) == $autumnal_equinox))  return TRUE; // 月曜日が敬老の日で、金曜日が秋分の日であるときの土曜日
}
return FALSE;
}
?>
