<?php


/** 
 * プラグインリスト一覧
 * #mypluglist プラグインディレクトリ下にあるファイルの情報一覧(:config/plugin/mypluglistで調整可能)
 * &mypluglist([optional_dir][,....]); package,subpackage別のリストを表示
 * @example http://lab01.positrium.org/index.php?PukiWiki%2F1.4%2FManual%2Faddons
 * @param string optional_dir プラグインディレクトリ直下のディレクトリ名
 * @version $Id$
 * @tutorial http://pukiwiki.sourceforge.jp/?%E8%87%AA%E4%BD%9C%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%2Fmypluglist.inc.php
 * @author token
 * @license http://www.gnu.org/licenses/gpl.ja.html GPL
 * @copyright Copyright &copy; 2007, positrium.org
 * $HeadURL$
 * @uses Config2_v1.1 http://svn.sourceforge.jp/svnroot/positrail/tags/Config2/1.1/Config2.inc.php
 * @package plugin
 * @subpackage info
 */
require_once (PLUGIN_DIR . 'org.positrium.lab01/Config2.inc.php');

class PluginMyPlugList {
	/** @access private */
	var $opt = array ();

	/** @access private */
	var $ignore = array ();

	/** @access private */
	var $config;

	function PluginMyPlugList() {
		$this->opt = array (
			'CONFIG_PATH' => 'plugin/mypluglist',
			'IGNORE_TITLE' => 'standard plugins',
			
		);

		$this->ignore = array (
			'add.inc.php',
			'amazon.inc.php',
			'aname.inc.php',
			'article.inc.php',
			'attach.inc.php',
			'back.inc.php',
			'backup.inc.php',
			'br.inc.php',
			'bugtrack.inc.php',
			'bugtrack_list.inc.php',
			'calendar.inc.php',
			'calendar2.inc.php',
			'calendar_edit.inc.php',
			'calendar_read.inc.php',
			'calendar_viewer.inc.php',
			'clear.inc.php',
			'color.inc.php',
			'comment.inc.php',
			'contents.inc.php',
			'counter.inc.php',
			'deleted.inc.php',
			'diff.inc.php',
			'dump.inc.php',
			'edit.inc.php',
			'filelist.inc.php',
			'freeze.inc.php',
			'hr.inc.php',
			'img.inc.php',
			'include.inc.php',
			'includesubmenu.inc.php',
			'insert.inc.php',
			'interwiki.inc.php',
			'lastmod.inc.php',
			'links.inc.php',
			'list.inc.php',
			'lookup.inc.php',
			'ls.inc.php',
			'ls2.inc.php',
			'map.inc.php',
			'md5.inc.php',
			'memo.inc.php',
			'menu.inc.php',
			'navi.inc.php',
			'new.inc.php',
			'newpage.inc.php',
			'nofollow.inc.php',
			'norelated.inc.php',
			'online.inc.php',
			'paint.inc.php',
			'pcomment.inc.php',
			'popular.inc.php',
			'random.inc.php',
			'read.inc.php',
			'recent.inc.php',
			'ref.inc.php',
			'referer.inc.php',
			'related.inc.php',
			'rename.inc.php',
			'rss.inc.php',
			'rss10.inc.php',
			'ruby.inc.php',
			'search.inc.php',
			'server.inc.php',
			'setlinebreak.inc.php',
			'showrss.inc.php',
			'size.inc.php',
			'source.inc.php',
			'stationary.inc.php',
			'tb.inc.php',
			'template.inc.php',
			'topicpath.inc.php',
			'touchgraph.inc.php',
			'tracker.inc.php',
			'tracker_list.inc.php',
			'unfreeze.inc.php',
			'update_entities.inc.php',
			'version.inc.php',
			'versionlist.inc.php',
			'vote.inc.php',
			'yetlist.inc.php',
			
		);

		$this->config = & new Config2($this->opt['CONFIG_PATH']);

		$init_flag = FALSE;

		if (!$this->config->is_title_exists($this->opt['IGNORE_TITLE'])) {
			$init_flag = TRUE;
		} else {
			if ($this->config->get($this->opt['IGNORE_TITLE']) == null) {
				$init_flag = TRUE;
			}
		}

		if ($init_flag) {
			$ignorearray = & new ConfigTable_Sequential2($this->opt['IGNORE_TITLE']);
			$ignorearray->values = $this->config->hash2config($this->ignore);
			$this->config->add_object($ignorearray);
			$this->config->write();
		}

	}

	/**
	 * @return Array 対象プラグインのファイル情報を含めた配列
	 */
	function collectPluginInfo($_dir) {
		$_reference_dir = $_dir; // . DIRECTORY_SEPARATOR;
		if ($dir = @ dir($_reference_dir)) {
			// doc comments
			$comments = array ();

			$obj = & $this->config->get_object($this->opt['IGNORE_TITLE']);
			while ($file = $dir->read()) {
				if (!preg_match("/\.(inc\.php)$/i", $file)) {
					// file が *.inc.php ではない時は次のfileへ
					continue;
				}

				$hash = $this->config->config2hash($obj->values);
				if (in_array($file, $hash)) {
					// ignore array に登録済みなら次のfileへ
					continue;
				}

				$docc_start = FALSE;
				$comment = null;
				foreach (file($_reference_dir . $file) as $line) {

					if (preg_match('/\/\*\*/', $line, $matches)) {
						$docc_start = TRUE;
						$comment = array (
							'file' => htmlspecialchars($file), 
							'summary' => array (), 
							'example' => '', 
							'params' => array (), 
							'version' => '', 
							'tutorial' => '', 
							'tutorial_cmd' => array (), 
							'author' => '', 
							'license' => '', 
							'copyright' => '', 
							'source' => '', 
							'uses' => array (), 
							'package' => '', 
							'subpackage' => '',
							);
					}

					/* doc comment is availe */
					if ($docc_start) {
						if (preg_match('/\*\//', $line, $matches)) {
							$comments[$file] = $comment;
							// */ = ブロックコメントの終端
							break;
						}
						elseif (preg_match('/\@example(.+)/', $line, $matches)) {
							$comment['example'] = htmlspecialchars(trim($matches[1]));

						}
						elseif (preg_match('/\@author(.+)/', $line, $matches)) {
							$comment['author'] = htmlspecialchars(trim($matches[1]));
						}
						elseif (preg_match('/\@version(.+)/', $line, $matches)) {
							$version = htmlspecialchars(trim($matches[1]));
							$comment['version'] = $version;
							if (preg_match('/\$Id:.+\.inc\.php(.+)\$/', $version, $n_matches)) {
								// cvs, svn形式のId があればrevision番号のみ取り出す
								$comment['version'] = htmlspecialchars(trim($n_matches[1]));
							}

						}
						elseif (preg_match('/\@tutorial(.+)/', $line, $matches)) {
							$comment['tutorial'] = htmlspecialchars(trim($matches[1]));

						}
						elseif (preg_match('/\@link(.+)/', $line, $matches)) {
							$comment['tutorial'] = htmlspecialchars(trim($matches[1]));

						}
						elseif (preg_match('/\@license(.+)/', $line, $matches)) {
							$license = explode(' ', htmlspecialchars(trim($matches[1])));
							$comment['license'] = $license[0];
							$comment['license_title'] = $license[1];

						}
						elseif (preg_match('/\@copyright(.+)/', $line, $matches)) {
							$comment['copyright'] = trim($matches[1]);

						}
						elseif (preg_match('/\@param (.+)/', $line, $matches)) {
							array_push($comment['params'], htmlspecialchars(trim($matches[1])));

						}
						elseif (preg_match('/\@uses (.+)/', $line, $matches)) {
							array_push($comment['uses'], htmlspecialchars(trim($matches[1])));

						}
						elseif (preg_match('/\@package (.+)/', $line, $matches)) {
							$comment['package'] = htmlspecialchars(trim($matches[1]));

						}
						elseif (preg_match('/\@subpackage (.+)/', $line, $matches)) {
							$comment['subpackage'] = htmlspecialchars(trim($matches[1]));

						}
						elseif (preg_match('/.*\*.*(\#.*)/', $line, $matches)) {
							// ブロック型
							// 本家のusageに相当する。
							array_push($comment['tutorial_cmd'], htmlspecialchars(trim($matches[1])));

						}
						elseif (preg_match('/(\&.*)/', $line, $matches)) {
							// インライン型
							// 本家のusageに相当する。
							array_push($comment['tutorial_cmd'], trim($matches[1]));

						}
						elseif (preg_match('/\$' . 'HeadURL: (http\:.+) \$/', $line, $matches)) {
							// ソースの取得元を取り出す。
							$comment['source'] = htmlspecialchars($matches[1]);
							// * $HeadURL$

						}
						elseif (preg_match('/.*\*' . '([^#$]+)/', $line, $matches)) {
							// 概要として取り出す。
							$summary = htmlspecialchars(trim($matches[1]));
							if (strlen($summary) > 0) {
								// 前後にも文中にも空白が無い場合
								array_push($comment['summary'], $summary);
							}
						}
					}
					/* doc comment is availe */
				}

			}
			$dir->close();

			// 全てのプラグインでdoc commentが一行も無ければ、'none'を返す。
			if (0 < count($comments)) {
				ksort($comments);
				foreach ($comments as $i => $comment) {
					ksort($comment);
					$comments[$i] = $comment;
				}
			}

		}
		return $comments;
	}

	/**
	 * ブロック型で記述すると呼ばれる
	 */
	function convert($args) {
		error_reporting(E_ALL & ~E_NOTICE);
		global $vars;

		$comments = $this->collectPluginInfo(PLUGIN_DIR);

//		print_r($comments);

		$script = get_script_uri();
		$s_page = $vars['page'];

		// 以降 出力フォーマットの定義

		// refresh用のリンクを出力

		$retVal2 = "#mypluglist\n";
		$retVal2 .= "#contents\n";
		$retVal2 .= "\n";

		// file 毎のdoc comment を出力する
		foreach ($comments as $comment) {
			$retVal2 .= "**" . $comment['file'] . "\n";
			$retVal2 .= ":概要|\n";

			// 複数行の概要(summary)を出力
			foreach ($comment['summary'] as $line) {
				$retVal2 .= $line . "~\n";
			}

			// 詳細書式ページへのリンク(tutorial)を出力
			$tut = "";
			if (preg_match('/http.+/', $comment['tutorial'], $matches)) {
				$tut = "([[see tutorial:" . $comment['tutorial'] . "]])";
			}
			$retVal2 .= ":書式" . $tut . "|";

			// 複数パターンの簡易書式(tutorial_cmd)を出力
			if (count($comment['tutorial_cmd']) > 0) {
				foreach ($comment['tutorial_cmd'] as $cmd) {
					$cmds = explode(' ', $cmd);
					if (count($cmds) > 0) {
						$retVal2 .= $cmds[1] . "\n";
					}
					$retVal2 .= " " . $cmds[0] . "\n";
				}
			}

			// 複数の引数(params)の概要を出力
			$retVal2 .= "\n:引数|\n";

			foreach ($comment['params'] as $param) {
				$paramvalue = explode(' ', $param);
				$retVal2 .= $paramvalue[0] . " ''" . $paramvalue[1] . "'' -- " . $paramvalue[2] . "~\n";
			}

			// 動作サンプルが動いているページへのリンクを出力
			$retVal2 .= ":動作サンプル|" . $comment['example'] . "\n";

			// 取得元ソース(source)のURLを整形して出力
			$source = "";
			if (!empty ($comment['source'])) {
				$source = "( [[source:" . $comment['source'] . "]] )";
			}
			$retVal2 .= ":バージョン|" . $comment['version'] . $source . "\n";
			$retVal2 .= ":著作者|" . $comment['author'] . "\n";
			$retVal2 .= ":著作権表示|" . $comment['copyright'] . "\n";

			// ライセンス表示(license_title, license)を整形して出力
			// デフォルトでは、[[ライセンス名:ライセンス条項のURL]] の様な形で出力される
			$license = "[[" . $comment['license_title'] . ":" . $comment['license'] . "]]";
			// ライセンス条項のURL が無い場合は、ライセンス名のみが出力される。
			if (empty ($comment['license_title'])) {
				$license = $comment['license'];
			}
			$retVal2 .= ":ライセンス|" . $license . "\n";

			if (count($comment['uses']) > 0) {
				$retVal2 .= ":依存関係|";
				foreach ($comment['uses'] as $uses) {
					$uses_value = explode(' ', $uses);
					$retVal2 .= "[[" . $uses_value[0] . ":" . $uses_value[1] . "]]~\n";
				}
			} else {
				$retVal2 .= ":依存関係|----\n";
			}
		}

		if (is_freeze($vars['page'])) {
			$retVal2 = "#freeze\n" . $retVal2;
		}
		page_write($vars['page'], $retVal2);
	}

	/**
	 * GET/POSTで呼ばれる
	 */
	function action($args) {

	}

	function sanitize_args($args){
		foreach($args as $i => $v){
			$args[$i] = trim($v);
			if(strlen($args[$i])==0){
				unset($args[$i]);
			}
		}
		
		return $args;
	}
	
	function inline($args) {
		$args = $this->sanitize_args($args);
		
		$info = array();
		$plug = array(
			'file' => '',
			'package' => '',
			'subpackage' => '',
			);
			
		$comments = $this->collectPluginInfo(PLUGIN_DIR);
		if(count($args)>0){
			foreach($args as $arg){
				$comments2 = $this->collectPluginInfo(PLUGIN_DIR.$arg."/");
				foreach($comments2 as $c){
					$comments[$c['file']] = $c;
				}
			}
		}
		
//		print_r($comments);
		
		$i = 0;
		foreach($comments as $comment){
			$plug['file'] = $comment['file'];
			$plug['package'] = $comment['package'];
			$plug['subpackage'] = $comment['subpackage'];
			
			$info[$i] = $plug;
			$i++;
		}
		
		$list = array();
		
		foreach($info as $ifo){
			if(strlen($ifo['package'])>0){
				if(strlen($ifo['subpackage'])>0){
					$list[$ifo['package']][$ifo['subpackage']][] = $ifo;
				}else{
					$list[$ifo['package']]['zzz'][] = $ifo;
				}
			}else{
				$list['zzz']['zzz'][] = $ifo;
			}
		}
		
//		print_r($list);
		$output = "";
		
		foreach($list as $i => $packages){
			$output[] = "\n\n''".$i."''\n";
			ksort($packages);
			$list[$i] = $packages;
			foreach($packages as $k => $subpackages){
				$output[] = "~[".$k."]~\n";
				foreach($subpackages as $s => $item){
					$output[] = "".$item['file']."\n";
				}
			}
		}
		
//		print_r($output);
//		print_r($list);
		return convert_html(join('',$output));
		
	}
}

/**
 * @ignore
 */
function plugin_mypluglist_init() {
	global $plugin_mypluglist;
	$plugin_mypluglist = new PluginMyPlugList();
}

/**
 * @ignore
 */
function plugin_mypluglist_convert() {
	global $plugin_mypluglist;
	$args = func_get_args();
	$args = & array_map('trim', $args);
	return $plugin_mypluglist->convert($args);
}

/**
 * @ignore
 */
function plugin_mypluglist_action() {
	global $plugin_mypluglist;
	$args = func_get_args();
	return $plugin_mypluglist->action($args);
}

/**
 * @ignore
 */
function plugin_mypluglist_inline() {
	global $plugin_mypluglist;
	$args = func_get_args();
	return $plugin_mypluglist->inline($args);
}
?>