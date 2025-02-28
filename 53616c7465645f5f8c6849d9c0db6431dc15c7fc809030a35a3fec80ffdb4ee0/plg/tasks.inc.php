<?php

//Jpgraphによるガントチャート表示を行うか？ true or false
    DEFINE("USE_JPGRAPH","false");

//Jpgraphを行う場合にガントチャートをリスト表示に使用するか？ true or false
    DEFINE("DEFAULT_JPGRAPH","true");

//jpgraph.phpとjpgraph_gantt.phpのパス("/"まで入力)
    DEFINE("JPGRAPH_PATH","/foo/bar/");
    
//iClalendarの出力パス("/"まで入力)
    DEFINE("ICS_PATH","/foo/bar/");
    
//iCalendarのURL("/"まで入力)
    DEFINE("ICS_URL","http://www.example.com/foor/bar/");
    
//iCalendarのカレンダー名
    DEFINE("CALENDAR_NAME","tasks");

//iCalendarでの進捗度を抑制
    DEFINE("ICS_PROGRESS_MAX","90");
    
//リストの最適化を自動で行うか？ true or false 未実装!!
    DEFINE("ALWAYS_REBUILD","true");

    DEFINE("VERSION","1.8");

    class plugin_tasks_export_for_ics{ //ics出力用クラス
        
        function plugin_tasks_export_for_ics($calendar_name = CALENDAR_NAME){
		    $this->CALENDAR_NAME = $calendar_name;
	    }
        
        function header(){
    	    define("VENDER","nobu_s");
    	    define("PRODUCT","pukiwiki_tasks_export_for_ical");
		    $header  = "BEGIN:VCALENDAR\r\n";
		    $header .= "CALSCALE:GREGORIAN\r\n";
		    $header .= "X-WR-TIMEZONE:Asia/Tokyo\r\n";
		    $header .= "X-WR-RELCALID:".md5(ICS_URL)."\r\n";
		    $header .= "PRODID:-//".VENDER."//".PRODUCT." Ver.".VERSION."//JP\r\n";
		    $header .= "X-WR-CALNAME:".$this->CALENDAR_NAME."\r\n";
		    $header .= "VERSION:2.0\r\n";
		    $header .= "BEGIN:VTIMEZONE\r\n";
		    $header .= "LAST-MODIFIED:".date("Ymd\THis")."\r\n";
		    $header .= "TZID:Asia/Tokyo\r\n";
		    $header .= "BEGIN:STANDARD\r\n";
		    $header .= "DTSTART:19371231T150000\r\n";
		    $header .= "TZOFFSETTO:+0900\r\n";
		    $header .= "TZNAME:JST\r\n";
		    $header .= "TZOFFSETFROM:+0000\r\n";
		    $header .= "END:STANDARD\r\n";
		    $header .= "END:VTIMEZONE\r\n";
		    return $header;
	    }
        function footer(){
		    $footer = "END:VCALENDAR\r\n";
		    return $footer;
	    }
        function update($time){
		    $time = $time-32400;
		    $this->eLastupdate = date("Ymd\THis", $time);
	    }
        function body($line){
            global $script;
		    $this->eData = "";
		    $url = $script."?"."cmd=read&page=".urlencode($line[0]);
            $parent = explode("/",$line[0]);
		    $this->eTitle = $line[1]."[".$parent[count($parent)-1]."]";
		    $this->eBody = "[".$line[0]."]";
		    $date = "VALUE=DATE:".preg_replace("/\//","",$line[2]);
            $date_comp = $this->unix_comp($line[2],$line[3]);
		    $duration = "P".$date_comp."D";
		    $this->eData .= ($line[6] != "on") ? "BEGIN:VEVENT\r\n" : "BEGIN:VTODO\r\n";
		    $this->eData .= "DTSTAMP:".$this->eLastupdate."\r\n";
		    $this->eData .= "SUMMARY:".$this->eTitle."\r\n";
		    $this->eData .= "DTSTART;".$date."\r\n";
		    $this->eData .= "UID:".md5(microtime())."\r\n";
		    $this->eData .= "DURATION:".$duration."\r\n";
            $this->eData .= "URL:".$url."\r\n";
		    $this->eData .= "DESCRIPTION:".$this->eBody."\r\n";
		    $this->eData .= ($line[6] != "on") ? "END:VEVENT\r\n" : "END:VTODO\r\n";
		    return $this->eData;
	    }
        function unix_comp($sdate,$edate){
		    $sdate_array = explode("/",$sdate);
		    $edate_array = explode("/",$edate);
		    $sdate_unix = mktime(0,0,0,abs($sdate_array[1]),abs($sdate_array[2]),$sdate_array[0]);
		    $edate_unix = mktime(0,0,0,abs($edate_array[1]),abs($edate_array[2])+1,$edate_array[0]);
		    return ($edate_unix-$sdate_unix)/60/60/24;
        }
    }
    
    function plugin_tasks_check_config(){ //初期設定の確認・作成
        global $contents_header;
    //コンテンツファイルの確認・作成
        $config = new Config("plugin/tasks/contents");
        if (!$config->read()){
            $body  = $contents_header;
            page_write(":config/plugin/tasks/contents",$body);
        }
         
    //内容設定ファイルの確認・作成
        $config = new Config("plugin/tasks");
        if (!$config->read()){
        //重要度の設定記入
            $body .= "*priority\n";
            $body .= "|~重要度|~表示内容|~デフォルト|~件名の色|h\n";
            $body .= "|70|重要||red|\n|50|普通|true|black|\n|30|低い||black|\n";
        //進捗度の設定記入
            $body .= "*progress\n";
            $body .= "|~進捗度|~表示内容|~デフォルト|h\n";
            $body .= "|100|完了||\n|75|調整中||\n|50|工事中||\n|35|機器発注済||\n|25|検討中||\n|0|未検討|true|\n";
        //設定書き込み
            page_write(":config/plugin/tasks",$body);
        }
    }
    
    function plugin_tasks_read_config($file,$category){ //初期設定ファイルの読込み
        $config = new Config($file);
        if($config->read()){
            return $config->get($category);
        }else{
            return false;
        }
    }
    function plugin_tasks_create_date($year,$mon,$day){ //yyyy/mm/ddの日付を作成
        $year = mb_convert_kana($year,"KVa");
        $mon  = mb_convert_kana($mon, "KVa");
        $day  = mb_convert_kana($day, "KVa");
        if ( abs($mon) > 12 ) $mon = 12;
        for ( $i = abs($day); $i > 0; $i--) {
            if ( checkdate(abs($mon), $i, $year) ) {
                $day = $i;
                break;
            }
        }
        return sprintf("%04d/%02d/%02d", $year, $mon, $day);
    }
    
    function plugin_tasks_check_date($date,$sep){ //一覧表示時に色別処理するための日付比較
        $today = getdate();
        $today_unix = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);
        $tmp_array = explode($sep,$date);
        $target_unix =  mktime(0,0,0,abs($tmp_array[1]),abs($tmp_array[2]),abs($tmp_array[0]));
        return ($target_unix < $today_unix) ? 0 : 1 ;
    }
    
    function plugin_tasks_get_unix_time($date){ //yyyy/mm/dd形式の日付をUNIX時間に変換
		$tmp_array = explode("/",$date);
		return mktime(0,0,0,abs($tmp_array[1]),abs($tmp_array[2]),$tmp_array[0]);
	}
    
    function plugin_tasks_get_gantt($page,$list,$srange,$erange){ //クライアントサイドイメージマップ付ガントチャートの作成
        //global $vars,$priority_list;
        global $vars;
        $jpgraph_php = JPGRAPH_PATH."jpgraph.php";
        $jpgraph_gantt_php = JPGRAPH_PATH."jpgraph_gantt.php";
        include_once ($jpgraph_php);
        include_once ($jpgraph_gantt_php);
        $week_check = 0;
        $sdate_unix_small = 0;
        $edate_unix_big   = 0;
        foreach($list as $i){
            $task_name_link[] = $script."?".urlencode($i[0]."/".$i[1]);
            $task_edit_link[] = $script."?plugin=tasks&amp;page=".urlencode($page)."&amp;task_name=".urlencode($i[1]);
		    $task_name[] = $i[1];
		    $sdate[] = $i[2];
		    $sdate_unix = plugin_tasks_get_unix_time($i[2]);
		    $edate[] = $i[3];
		    $edate_unix = plugin_tasks_get_unix_time($i[3]);
            $priority[] = $i[4];
            $progress[] = $i[5]/100;
            if($sdate_unix_small ==0)           $sdate_unix_small = $sdate_unix;
            if($sdate_unix_small > $sdate_unix) $sdate_unix_small = $sdate_unix;
            if($edate_unix_big ==0)             $edate_unix_big   = $edate_unix_big;
            if($edate_unix_big < $edate_unix)   $edate_unix_big   = $edate_unix;
        }
        if($srange != 0 && $erange != 0){
            $sdate_unix_small = plugin_tasks_get_unix_time($srange);
            $edate_unix_big = plugin_tasks_get_unix_time($erange);
        }
        array_multisort ($sdate,$task_name,$edate,$task_name_link,$progress,$priority,$task_name_link,$task_edit_link);
    	if(($edate_unix_big - $sdate_unix_small) > (60 * 60 * 24 * 30 * 4)){
    		$week_check = 2;
    	}elseif((($edate_unix_big - $sdate_unix_small) > (60 * 60 * 24 * 30 * 2))){
    		$week_check = 1;
    	}
        
        $graph = new GanttGraph(600,0,"auto",5);
        //$graph->SetBox();
        //$graph->SetShadow();
        //$graph->SetMargin(40,20,20,0);
        // Add title and subtitle
        $graph->title->Set(preg_replace("/^tasks\//","",$page));
        $graph->title->SetFont(FF_FONT1,FS_BOLD,10);
        //$graph->subtitle->Set("(ganttex16.php)");

        // Show day, week and month scale
        if($week_check == 1){
        	$graph->ShowHeaders(GANTT_HWEEK | GANTT_HMONTH);
        	$graph->scale-> month-> SetStyle( MONTHSTYLE_SHORTNAMEYEAR2); 
        	$graph->scale-> week->SetStyle(WEEKSTYLE_FIRSTDAY);
            $graph->SetDateRange(
                date("Y/m/d",$sdate_unix_small - (60*60*24*3)),
                date("Y/m/d",$edate_unix_big + (60*60*24*3))
            );
        }elseif($week_check == 0){
        	$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        	$graph->scale-> month-> SetStyle( MONTHSTYLE_SHORTNAMEYEAR2); 
        	$graph->scale-> week->SetStyle(WEEKSTYLE_FIRSTDAY); 
            $graph->SetDateRange(
                date("Y/m/d",$sdate_unix_small - (60*60*24*3)),
                date("Y/m/d",$edate_unix_big + (60*60*24*3))
            );
        }elseif($week_check == 2){
        	$graph->ShowHeaders(GANTT_HYEAR);
        	$graph->scale-> month-> SetStyle( MONTHSTYLE_SHORTNAMEYEAR2); 
            $graph->SetDateRange(
                date("Y/m/d",$sdate_unix_small - (60*60*24*3)),
                date("Y/m/d",$edate_unix_big + (60*60*24*45))
            );
        }


        // Set table title
        //$graph->scale->tableTitle->Set("(Rev: 1.22)");
        //$graph->scale->tableTitle->SetFont(FF_FONT1,FS_BOLD);
        //$graph->scale->SetTableTitleBackground("white");
        //$graph->scale->tableTitle->Show();

        // Use the short name of the month together with a 2 digit year
        // on the month scale
        //$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR2);
        $graph->scale->month->SetFontColor("black");
        $graph->scale->month->SetBackgroundColor("white");

        // 0 % vertical label margin
        $today = getdate();
        $tody_str = $today["year"]."-".$today["mon"]."-".$today["mday"];
        $graph->SetLabelVMarginFactor(0.5);
        $page_tmp = "";
        for($i=0; $i<count($task_name); ++$i){
        	// Format the bar for the first activity
        	// ($row,$title,$startdate,$enddate)
            $task_name15 = sprintf("% -15s",substr($task_name[$i],0,15));
        	$activity{$i} = new GanttBar($i,$task_name15,$sdate[$i],$edate[$i],$edate[$i]);
        	$activity{$i}->SetPattern(BAND_RDIAG,"silver");

        	// Set absolute height
        	$activity{$i}->SetHeight(10);
            $activity{$i}->SetCSIMTarget($task_edit_link[$i]);
            $activity{$i}->SetCSIMAlt($task_name[$i]."の詳細を編集");
            $activity{$i}->title->SetCSIMTarget($task_name_link[$i]);
            $activity{$i}->title->SetCSIMAlt($task_name[$i]."を編集");
            $activity{$i}->progress->Set($progress[$i]);
            //$activity{$i}->progress->SetPattern(BAND_RDIAG,$priority_list[$priority[$i]]["color"]);
            $activity{$i}->progress->SetPattern(BAND_RDIAG,plugin_tasks_priority_property($page,$priority[$i],"color"));
        	$graph->Add($activity{$i});
        }


    // Add a vertical line
        
        $vline = new GanttVLine($tody_str,"Today",red,1.5);
        //$vline->SetDayOffset(0.1);
        $graph->Add($vline);

        // ... and display it
        $graph->Stroke("cache/".md5($page).".png");
        $return_str .= $graph->GetHTMLImageMap(md5($page))."\n\n";
        $return_str .= "<img src=\"".CACHE_DIR.md5($page).".png\" ismap=\"ismap\" usemap=\"#".md5($page)."\" alt=\"gantt_chart_map\" />";
    
        return $return_str;
    }
    
    function plugin_tasks_create_content_body($list){ //:config/plugin/tasks/contents用のテーブル作成
        foreach($list as $i){
            if($i[0] != "") { //ページ名が空白のときはリストから削除
                $return_body .= "|".$i[0]."|".$i[1]."|".$i[2]."|".$i[3]."|".$i[4]."|".$i[5]."|".$i[6]."|\n";
            }
        }
        return $return_body;
    }
    
    function plugin_tasks_get_child_list($page,$srange=0,$erange=0){ //タスク一覧作成
        //global $script,$get,$tasks_contents_list,$vars,$priority_list;
        global $script,$get,$tasks_contents_list,$vars;
        foreach($tasks_contents_list as $i){ //指定ページに対応するタスクの選択
		    if($i[0] == $page) $child_list[] = $i;
        }
        if(((DEFAULT_JPGRAPH == "true" && USE_JPGRAPH == "true") && !isset($get["gantt"]))or $get["gantt"] == "true"){ //ガントチャートを選択
            if(count($child_list) > 0){
                $return_str = convert_html($return_str);
                $gantt_str .= plugin_tasks_get_gantt($page,$child_list,$srange,$erange);
                $return_str .= $gantt_str;
            }else{
                $gantt_str .= "タスクはありません。";
                $return_str .= $gantt_str;
                $return_str = convert_html($return_str);
            }
        }else{ //HTMLテーブルを選択
            $return_str .= "|~タスク|~開始日時|~終了日時|~ |h\n";
            foreach($child_list as $i){
            //開始日が過ぎているときは開始日を赤表示
                $scolor = (plugin_tasks_check_date($i[2],"/") == 0) ? "COLOR(red):" : "";
            //終了日が過ぎているときは終了日を赤表示
                $ecolor = (plugin_tasks_check_date($i[3],"/") == 0) ? "COLOR(red):" : "";
            //重要度の色を反映
                //$pcolor = "COLOR(".$priority_list[$i[4]]["color"]."):";
                $pcolor = "COLOR(".plugin_tasks_priority_property($i[0],$i[4],"color")."):";
                $return_str .= "|[[".$pcolor.$i[1].">".$script."?".urlencode($i[0]."/".$i[1])."]]";
                $return_str .= "|".$scolor.$i[2];
                $return_str .= "|".$ecolor.$i[3];
                $return_str .= "|[["."詳細>".$script."?plugin=tasks&page=".urlencode($page)."&task_name=".urlencode($i[1])."]]";
                $return_str .= "|\n";
            }
            $return_str = convert_html($return_str);
        }
        return $return_str."<br />";
    }
    
    function plugin_tasks_progress_property($page,$num,$property){
        global $progress_list;
        if(!empty($progress_list[$page][$num][$property])){
            return $progress_list[$page][$num][$property];
        }elseif(!empty($progress_list[""][$num][$property])){
            return $progress_list[""][$num][$property];
        }else{
            return $num;
        }
    }
    
    function plugin_tasks_priority_property($page,$num,$property){
        global $priority_list;
        if(!empty($priority_list[$page][$num][$property])){
            return $priority_list[$page][$num][$property];
        }elseif(!empty($priority_list[""][$num][$property])){
            return $priority_list[""][$num][$property];
        }else{
            return $num;
        }
    }
    function plugin_tasks_init(){ //プラグイン初期動作
        global $tasks_contents_list,$contents_header,$priority_list,$progress_list;
    //設定ファイルの確認
        plugin_tasks_check_config();
    //タスクリストの取得
        $tasks_contents_list = plugin_tasks_read_config("plugin/tasks/contents","contents_list");
    //タスクリストのヘッダー設定
        $contents_header  = "*contents_list [#sd115b27]\n\n";
        $contents_header .= "|~ページ|~タスク|~開始日時|~終了日時|~重要度|~進捗度|~TODO|h\n";
    //重要度の色と文字を取得
        $priority_array = plugin_tasks_read_config("plugin/tasks","priority");
        if($priority_array != false){
            foreach($priority_array as $i){
                $priority_list[""][$i[0]]["color"] = $i[3];
                $priority_list[""][$i[0]]["string"] = $i[1];
            }
        }
    //進捗度の色と文字を取得
        $progress_array = plugin_tasks_read_config("plugin/tasks","progress");
        if($progress_array != false){
            foreach($progress_array as $i){
                $progress_list[""][$i[0]]["string"] = $i[1];
                $progress_list[""][$i[0]]["value"] = $i[0];
            }
        }
    //個別の設定ファイル検索
        foreach($tasks_contents_list as $i){
            $config_file_array[] = $i[0];
        }
        $config_file_array = array_unique($config_file_array);
        //for($j=0; $j<count($config_file_array); ++$j){
        foreach($config_file_array as $key => $val){
            $priority_array = plugin_tasks_read_config("plugin/tasks/property/".$val,"priority");
            if($priority_array != false){
                foreach($priority_array as $i){
                    $priority_list[$val][$i[0]]["color"] = $i[3];
                    $priority_list[$val][$i[0]]["string"] = $i[1];
                }
            }
            $progress_array = plugin_tasks_read_config("plugin/tasks/property/".$val,"progress");
            if($progress_array != false){
                foreach($progress_array as $i){
                    $progress_list[$val][$i[0]]["string"] = $i[1];
                    $progress_list[$val][$i[0]]["value"] = $i[0];
                }
            }
        }
        //print_r($progress_list);
    }
    

    function plugin_tasks_action(){ //プラグインが単独で呼ばれた場合
        //global $tasks_contents_list,$get,$post,$script,$get,$contents_header,$progress_list;
        global $tasks_contents_list,$get,$post,$script,$get,$contents_header;
        if($get["mode"] == "property"){
            if(!(plugin_tasks_read_config("plugin/tasks/property/".$get["page"],"priority")
                    or plugin_tasks_read_config("plugin/tasks/property/".$get["page"],"progress"))){
                $def_source = get_source(":config/plugin/tasks");
                for($i=0; $i<count($def_source); ++$i){
                    $def_body .= $def_source[$i];
                }
                page_write(":config/plugin/tasks/property/".$get["page"],$def_body);
            }
            header("Location: $script?cmd=read&page=".urlencode(":config/plugin/tasks/property/".$get["page"]));
        }
        if($get["export"] == "ical"){ //ics出力
            $ics_url  = ICS_URL.CALENDAR_NAME.".ics";
            $ics_path = ICS_PATH.CALENDAR_NAME.".ics";
            $progress_complate = 0;
            //foreach($progress_list as $i){
            //    $progress_complate = ($i["value"] > $progress_complate) ? $i["value"] : $progress_complate;
            //}
            $ical_export = new plugin_tasks_export_for_ics();
            $body = $ical_export->header();
            foreach($tasks_contents_list as $i){
                if($i[5] <= ICS_PROGRESS_MAX){
			        $update = get_filetime($i[0]);
			        $ical_export->update($update);
                    $body .= $ical_export->body($i);
                }
            }
            $body .= $ical_export->footer();
            if($get["dummy"] == "ics"){
	            $utf_body = mb_convert_encoding($body, "UTF-8", "EUC-JP");
	            $fp = fopen($ics_path, 'w');
	            fputs($fp,$utf_body);
	            fclose($fp);
                header("Location: ".$ics_url);
            }else{
                return array("msg"=>$ics_url."の内容", "body"=>"<pre>".$body."</pre>");
            }
        }
        if($post["mode"] == "rename_exec"){ //名前の変更を実行
            $urldecode_page = urldecode($post["new_page"]);
            $body  = $contents_header;
            $exist = is_page($post["new_page"]);
            if($exist == true && $post["page"] != $post["new_page"]){
                return array("msg" => "エラー", "body" => $post["new_page"]."はすでに存在します。");
            }
            if($post["page"] != $post["new_page"]){
                for($i=0; $i<count($tasks_contents_list); ++$i){
                    if($tasks_contents_list[$i][0] == $post["page"]){
                        $tasks_contents_list[$i][0] = $post["new_page"].$exist;
                    }
                }
                $body .= plugin_tasks_create_content_body($tasks_contents_list);
                page_write(":config/plugin/tasks/contents",$body);
                $page_source = get_source($post["page"]);
                $body = "";
                for($i=0; $i<count($page_source); ++$i){
                    $body .= $page_source[$i];
                }
                page_write($post["page"],"");
                page_write($post["new_page"],$body);
            }
            header("Location: $script?cmd=read&page=$urldecode_page");
        }
        if($get["mode"] == "rename"){ //名前の変更用フォーム表示
            $body  = urldecode($get["page"])." の名称を変更します。";
            $body .= "
                <form enctype=\"multipart/form-data\" action=\"$script?plugin=tasks\" method=\"post\">
                    <div>
                        <input type name=\"new_page\" size=\"80\" value=\"".urldecode($get["page"])."\" />
                        <input type=\"submit\" name=\"submit\" value=\"変更\" />
                        <input type=\"hidden\" name=\"mode\" value=\"rename_exec\" />
                        <input type=\"hidden\" name=\"page\" value=\"".urldecode($get["page"])."\" />
                    </div>
                </form>";
            return array("msg" => "ページ名の変更", "body" => $body);
        }
        if($post["mode"] == "edit"){ //タスク変更
            $body  = $contents_header;
            $edit_flag ="false";
            $tasks_count = count($tasks_contents_list);
            for($i=0; $i<$tasks_count; ++$i){
                if($post["page"] == $tasks_contents_list[$i][0] && $post["task_name"] == $tasks_contents_list[$i][1]){
                    $edit_flag = "ture";
                    $tasks_contents_list[$i][1] = $post["new_task_name"];
                    $tasks_contents_list[$i][2] = plugin_tasks_create_date(
                        $post["syear"],$post["smonth"],$post["sday"]);
                    $tasks_contents_list[$i][3] = plugin_tasks_create_date(
                        $post["eyear"],$post["emonth"],$post["eday"]);
                    $tasks_contents_list[$i][4] = $post["priority"];
                    $tasks_contents_list[$i][5] = $post["progress"];
                    $tasks_contents_list[$i][6] = $post["todo"];
                    if(isset($_POST["delete"])) $tasks_contents_list[$i][0] = ""; //削除のときはページ名を削除
                }
            }
            if($edit_flag == "false"){
                $num = $tasks_count;
                $tasks_contents_list[$num][0] = $post["page"];
                $tasks_contents_list[$num][1] = $post["new_task_name"];
                $tasks_contents_list[$num][2] = 
                    plugin_tasks_create_date($post["syear"],$post["smonth"],$post["sday"]);
                $tasks_contents_list[$num][3] = 
                    plugin_tasks_create_date($post["eyear"],$post["emonth"],$post["eday"]);
                $tasks_contents_list[$num][4] = $post["priority"];
                $tasks_contents_list[$num][5] = $post["progress"];
                $tasks_contents_list[$num][6] = $post["todo"];
            }
            $body .= plugin_tasks_create_content_body($tasks_contents_list);
            page_write(":config/plugin/tasks/contents",$body);
            $encode_post_page = urlencode($post["page"]);
            header("Location: $script?cmd=read&page=".$encode_post_page);
        }
        if($get["mode"] == "rebuild"){ //リストの最適化実施
            $header  = $contents_header;
            foreach($tasks_contents_list as $i){
                $page[]      = $i[0];
                $task_name[] = $i[1];
                $sdate[]     = $i[2];
                $edate[]     = $i[3];
                $priority[]  = $i[4];
                $progress[]  = $i[5];
                $is_todo[]  = $i[6];
            }
            array_multisort($page,$sdate,$task_name,$edate,$priority,$progress,$is_todo);
            $body  = $header;
            for($i=0; $i<count($page); ++$i){
                $body .= "|".$page[$i];
                $body .= "|".$task_name[$i];
                $body .= "|".$sdate[$i];
                $body .= "|".$edate[$i];
                $body .= "|".$priority[$i];
                $body .= "|".$progress[$i];
                $body .= "|".$is_todo[$i]."|\n";
            }
            page_write(":config/plugin/tasks/contents",$body);
            $urldecode_page = urldecode($get["page"]);
            header("Location: $script?cmd=read&page=$urldecode_page");
        }
        if(isset($get["page"]) && isset($get["task_name"])){ //タスクの追加と変更用フォーム表示
            $get["page"]      = urldecode($get["page"]);
            $get["task_name"] = urldecode($get["task_name"]);
            list($syear,$smonth,$sday,$eyear,$emonth,$eday) =
                array(get_date("Y"),get_date("m"),get_date("d"),get_date("Y"),get_date("m"),get_date("d"));
            $progress = "";
            $priority = "";
            $contents_array = $tasks_contents_list;
            foreach($contents_array as $i){
                if($i[0] == $get["page"] && $i[1] == $get["task_name"]){
                    list($syear,$smonth,$sday) = explode("/",$i[2]);
                    list($eyear,$emonth,$eday) = explode("/",$i[3]);
                    $priority = $i[4];
                    $progress = $i[5];
                    $todo_select = ($i[6] == "on") ? " checked=\"checked\"" : "";
                }
            }
            //セレクトボックスの作成
            $select_box_array = array("progress","priority");
            for($j=0; $j<count($select_box_array); ++$j){
                if(plugin_tasks_read_config("plugin/tasks/property/".$get["page"],$select_box_array[$j])){
                    ${$select_box_array[$j]."_array"} = plugin_tasks_read_config("plugin/tasks/property/".$get["page"]
                            ,$select_box_array[$j]);
                }else{
                    ${$select_box_array[$j]."_array"} = plugin_tasks_read_config("plugin/tasks",$select_box_array[$j]);
                }
                ${$select_box_array[$j]."_true"} = 0;
                ${$select_box_array[$j]."_html"} = "";
                for ($i=0; $i<count(${$select_box_array[$j]."_array"}); ++$i){
                    if(${$select_box_array[$j]} == "" && ${$select_box_array[$j]."_array"}[$i][2] =="true"){
                        $selected = " selected=\"selected\"";
                        ${$select_box_array[$j]."_true"} = 1;
                    }
                    if(${$select_box_array[$j]} != "" && ${$select_box_array[$j]."_array"}[$i][0] == ${$select_box_array[$j]}){
                        $selected = " selected=\"selected\"";
                        ${$select_box_array[$j]."_true"} = 1;
                    }
                    ${$select_box_array[$j]."_str"} = ${$select_box_array[$j]."_array"}[$i][1];
                    ${$select_box_array[$j]."_id"} = ${$select_box_array[$j]."_array"}[$i][0];
                    ${$select_box_array[$j]."_html"} .= "<option value=\"".${$select_box_array[$j]."_id"}."\"".$selected.">";
                    ${$select_box_array[$j]."_html"} .= ${$select_box_array[$j]."_str"};
                    ${$select_box_array[$j]."_html"} .= "</option>";
                    $selected = "";
                }
                if(${$select_box_array[$j]} != "" && ${$select_box_array[$j]."_true"} == 0){
                    ${$select_box_array[$j]."_html"} .= "<option value=\"".${$select_box_array[$j]}."\" selected=\"selected\">".${$select_box_array[$j]}."</option>";
                }
            }
            $submit_button = (isset($_GET[task_name]) && $_GET[task_name] == "") ?
                    "<input type=\"submit\" name=\"submit\" value=\"追加\" />" :
                    "<input type=\"submit\" name=\"submit\" value=\"変更\" /> \n <input type=\"submit\" name=\"delete\" value=\"削除\" />"; //タスク削除用
            $body ="
                <h2>Task入力</h2>
                    <form enctype=\"multipart/form-data\" action=\"$script?plugin=tasks\" method=\"post\">
                        <div align=\"center\">
                        <table>
                            <tr><td align=\"right\">件名：</td><td><input type name=\"new_task_name\" size=\"50\" value=\"".$get["task_name"]."\" /></td></tr>
                            <tr><td align=\"right\">開始日時：</td><td>
                                <input type=\"text\" name=\"syear\" size=\"4\" value=\"$syear\" />年
                                <input type=\"text\" name=\"smonth\" size=\"2\" value=\"$smonth\" />月
                                <input type=\"text\" name=\"sday\" size=\"2\" value=\"$sday\" />日
                            <td></tr>
                                <tr><td align=\"right\">終了日時：</td>
                                    <td>
                                        <input type=\"text\" name=\"eyear\" size=\"4\" value=\"$eyear\" />年
                                        <input type=\"text\" name=\"emonth\" size=\"2\" value=\"$emonth\" />月
                                        <input type=\"text\" name=\"eday\" size=\"2\" value=\"$eday\" />日
                                    <td></tr>
                                <tr><td align=\"right\">重要度：</td>
                                    <td><select name=\"priority\">
                                    $priority_html
                                    </select></td></tr>
                                <tr><td align=\"right\">進捗度：</td>
                                    <td><select name=\"progress\">
                                    $progress_html
                                    </select></td></tr>
                                <tr><td align=\"right\">TODO項目：</td>
                                    <td><input type=\"checkbox\" name=\"todo\"".$todo_select." />TODO項目にする</td></tr>
                        </table>
                        <input type=\"hidden\" name=\"task_name\" value=\"".$get["task_name"]."\" />
                        <input type=\"hidden\" name=\"page\" value=\"".urldecode($get["page"])."\" />
                        <input type=\"hidden\" name=\"mode\" value=\"edit\" />
                        $submit_button
                        </div>
                    </form>";
            if(isset($_GET[task_name]) && $_GET[task_name] == ""){
                return array("msg" => $get["page"]."にタスク追加", "body" => $body);
            }else{
                return array("msg" => $get["page"]."のタスク変更", "body" => $body);
            }
        }
    }
    
    function plugin_tasks_ematch($search,$string){ //preg_match用日本語対策
        if(preg_match("/".urlencode($search)."/i",urlencode($string))){
            return true;
        }else{
            return false;
        }
    }
    
    function plugin_tasks_convert(){  //プラグインがwiki内でで呼ばれた場合
        //global $script,$vars,$tasks_contents_list,$get,$priority_list,$progress_list,$post;
        global $script,$vars,$tasks_contents_list,$get,$post;
        $args = func_get_args();
        $tasks_header = "|~ページ|~タスク|~開始日時|~終了日時|~重要度|~進捗度|~Todo|h\n";
        ${$post["query"]."_select_value"} = " selected=\"selected\"";
        if(count($args) == 1 && $args[0] == "search"){ //検索ページ
            $query_str  = "<select name=\"query\">";
            $query_str .= "<option value=\"page\" $page_select_value>ページ</option>";
            $query_str .= "<option value=\"task\" $task_select_value>タスク</option>";
            $query_str .= "<option value=\"date\" $date_select_value>日時</option>";
            $query_str .= "<option value=\"priority\" $priority_select_value>重要度</option>";
            $query_str .= "<option value=\"progress\" $progress_select_value>進捗度</option>";
            $query_str .= "</select>";
            $form_str  = convert_html("**検索条件\n");
            $form_str .= "
<form enctype=\"multipart/form-data\" action=\"".$script."?".$vars["page"]."\" method=\"post\"><div>
    ".$query_str." に <input type=\"text\" name=\"query_value\" value=\"".$post["query_value"]."\" /> を含む項目 
    <input type=\"submit\" name=\"submit\" value=\"検索\" />
</div></form>";
            $body_str  = "**検索結果\n";
            $body_str .= $tasks_header;
            $result_value = 0;
            foreach($tasks_contents_list as $i){
                $query_check = 0;
                if($post["query"] == "page" && plugin_tasks_ematch($post["query_value"],$i[0])){
                    $query_check = 1;
                }
                if($post["query"] == "task" && plugin_tasks_ematch($post["query_value"],$i[1])){
                    $query_check = 1;
                }
                if($post["query"] == "date"
                        && plugin_tasks_get_unix_time($i[2]) <= plugin_tasks_get_unix_time($post[str_replace("-","/","query_value")])
                        && plugin_tasks_get_unix_time($i[3]) >= plugin_tasks_get_unix_time($post[str_replace("-","/","query_value")]) ){
                    $query_check = 1;
                }
                if($post["query"] == "priority" 
                        && ($post["query_value"] == $i[4] 
//                        or $priority_list[$i[4]]["string"] == $post["query_value"])){
                        or plugin_tasks_priority_property($i[0],$i[4],"string"))){
                    $query_check = 1;
                }
                if($post["query"] == "progress" 
                        && ($post["query_value"] == $i[5] 
//                        or $progress_list[$i[5]]["string"] == $post["query_value"])){
                        or plugin_tasks_progress_property($i[0],$i[4],"string"))){
                    $query_check = 1;
                }
                $scolor = (plugin_tasks_check_date($i[2],"/") == 0) ? "COLOR(red):" : "";
                $ecolor = (plugin_tasks_check_date($i[3],"/") == 0) ? "COLOR(red):" : "";
                //$priority_color = "COLOR(".$priority_list[$priority[$i]]["color"]."):";
                $priority_color = "COLOR(".plugin_tasks_priority_property($i[0],$i[4],"color")."):";
                if($query_check == 1){
                    $body_str .= "|".$i[0];
                    $body_str .= "|".$i[1];
                    $body_str .= "|".$i[2];
                    $body_str .= "|".$i[3];
                    $body_str .= "|".$priority_color.plugin_tasks_priority_property($i[0],$i[4],"string");
                    $body_str .= "|".plugin_tasks_progress_property($i[0],$i[5],"string");
                    $body_str .= "|".$i[6]."|\n";
                    $result_value = 1;
                }
                $priority_color = "";
            }
            if($result_value == 0){
                $body_str .= "||||||||\n";
            }
            $body_str = convert_html($body_str);
            return $form_str.$body_str;
        }
        if(count($args) == 1 && $args[0] == "all_list"){ //全タスクリスト表示
            foreach($tasks_contents_list as $i){
                $page[] = $i[0];
                $task_name[] = $i[1];
                $sdate[] = $i[2];
                $edate[] = $i[3];
                $priority[] = $i[4];
                $progress[] = $i[5];
                $todo[] = $i[6];
            }
            array_multisort($page,$sdate,$task_name,$edate,$priority,$progress);
            $body = $tasks_header;
            $page_tmp = "";
            for($i=0; $i<count($page); ++$i){
                if($page[$i] != $page_tmp){
                    if(urldecode($get["expand_page"]) != $page[$i]){
                        $expand_link = "[[ + >".$script."?cmd=read&page=".urlencode($vars["page"]).
                                            "&expand_page=".urlencode($page[$i])."]] ";
                    }else{
                        $expand_link = "[[ - >".$script."?cmd=read&page=".urlencode($vars["page"])."]] ";
                    }
                    $body .= "|>|>|>|>|>|>|".$expand_link.$page[$i]."|\n";
                }
                if(urldecode($get["expand_page"]) == $page[$i]){
                    $scolor = (plugin_tasks_check_date($sdate[$i],"/") == 0) ? "COLOR(red):" : "";
                    $ecolor = (plugin_tasks_check_date($edate[$i],"/") == 0) ? "COLOR(red):" : "";
                    //$priority_color = "COLOR(".$priority_list[$priority[$i]]["color"]."):";
                    $priority_color = "COLOR(".plugin_tasks_priority_property($page[$i],$priority[$i],"color")."):";
                    $body .= "| ";
                    $body .= "|"." [[".$priority_color.$task_name[$i].">".$script."?plugin=tasks&page=".urlencode($page[$i])."&task_name=".urlencode($task_name[$i])."]]";
                    $body .= "|".$scolor.$sdate[$i];
                    $body .= "|".$ecolor.$edate[$i];
                    //$body .= "|".$priority_color.$priority_list[$priority[$i]]["string"];
                    $body .= "|".$priority_color.plugin_tasks_priority_property($page[$i],$priority[$i],"string");
                    //$body .= "|".$progress_list[$progress[$i]]["string"];
                    $body .= "|".plugin_tasks_progress_property($page[$i],$progress[$i],"string");
                    $body .= "|".$todo[$i]."|\n";
                }
                $priority_color = "";
                $page_tmp = $page[$i];
            }
            return convert_html($body);
        }
        if(count($args) == 0){ //タスク一覧表示
            $header_bar .= "**タスク一覧";
            
            $command_bar .= "[ ";
            $command_bar .= "[[設定ページ編集>".$script."?plugin=tasks&page=".urlencode($vars["page"])."&mode=property]]";
            $command_bar .= " | ";
            $command_bar .= "[[ページ名の変更>".$script."?plugin=tasks&page=".urlencode($vars["page"])."&mode=rename]]";
            $command_bar .= " | ";
            $command_bar .= "[[リストの最適化>".$script."?plugin=tasks&page=".urlencode($vars["page"])."&mode=rebuild]]";
            $command_bar .= " ]";
            
            $tool_bar .= "[ ";
            $tool_bar .= "[[タスク追加>".$script."?plugin=tasks&page=".urlencode($vars["page"])."&task_name]]";
            $tool_bar .= " ] ";
            
	        if(USE_JPGRAPH == "true"){
	            $tool_bar .= " [ ";
	            $tool_bar .= "[[テーブル>".$script."?cmd=read&page=".urlencode($vars["page"])."&gantt=false]]";
	            $tool_bar .= " | ";
	            $tool_bar .= "[[ガントチャート>".$script."?cmd=read&page=".urlencode($vars["page"])."&gantt=true]]";
	            $tool_bar .= " ] ";
	        }
            
            //$return_body .= "\n\n";
            //$return_body = convert_html($return_body);
            $gantt_chart .= plugin_tasks_get_child_list($vars["page"]);
            $return_body = convert_html($header_bar."\n\n")
                    .convert_html($tool_bar."\n\n")."<br />".$gantt_chart.convert_html("RIGHT:".$command_bar."\n\n")."<br />";
            
        }else{ //マルチページ表示
            $count = count($args);
            
            for($i = 2; $i < $count; ++$i){
                $return_body .= convert_html("**".$args[$i]."\n").plugin_tasks_get_child_list($args[$i],$args[0],$args[1])."\n";
            }
            //$return_body = convert_html($return_body);
        }
        return $return_body;
        
    }
?>
