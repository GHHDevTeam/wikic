<?php
/**
 * Squirrel 2.x キーワード定義ファイル
 *
 * Modified:   <09/11/20 ruche>
 */

$switchHash['\''] = PLUGIN_CODE_STRING_LITERAL;     // HACK: '〜' も文字列リテラル扱い(実際は文字リテラル)

// コメント定義
$switchHash['/'] = PLUGIN_CODE_COMMENT;        //  コメントは /* から */ までと // から改行まで

$code_comment = Array(
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
				 Array('/^\/\//', "\n", 1),
	)
);

// アウトライン用
if($mkoutline){
	$switchHash['{'] = PLUGIN_CODE_BLOCK_START;
	$switchHash['}'] = PLUGIN_CODE_BLOCK_END;
}

$code_css = Array(
	'operator',		// -
	'identifier',	// キーワード
	'pragma',		// -
	'system',		// 可変長引数, メタメソッド, コンストラクタ
	'outline',
	);

$code_keyword = Array(
	// キーワード
	'break' => 2,
	'case' => 2,
	'catch' => 2,
	'class' => 2,
	'clone' => 2,
	'const' => 2,
	'continue' => 2,
	'default' => 2,
	'delegate' => 2,
	'delete' => 2,
	'do' => 2,
	'else' => 2,
	'enum' => 2,
	'extends' => 2,
	'function' => 2,
	'for' => 2,
	'foreach' => 2,
	'if' => 2,
	'in' => 2,
	'instanceof' => 2,
	'local' => 2,
	'null' => 2,
	'parent' => 2,
	'resume' => 2,
	'return' => 2,
	'static' => 2,
	'switch' => 2,
	'this' => 2,
	'throw' => 2,
	'try' => 2,
	'typeof' => 2,
	'while' => 2,
	'yield' => 2,

	// 真偽値
	'true' => 2,
	'false' => 2,

	// 可変長引数
	'vargc' => 4,
	'vargv' => 4,

	// メタメソッド
	'_add' => 4,
	'_sub' => 4,
	'_mul' => 4,
	'_div' => 4,
	'_unm' => 4,
	'_modulo' => 4,
	'_set' => 4,
	'_get' => 4,
	'_typeof' => 4,
	'_cmp' => 4,
	'_call' => 4,
	'_cloned' => 4,
	'_nexti' => 4,
	'_newslot' => 4,
	'_delslot' => 4,
	'_tostring' => 4,
	'_newmember' => 4,
	'_inherited' => 4,

	// コンストラクタ
	'constructor' => 4,

	// 以下、必要であれば有効にしてください。

/*
	// 組み込み関数
	'array' => 4,
	'seterrorhandler' => 4,
	'setdebughook' => 4,
	'enabledebuginfo' => 4,
	'getroottable' => 4,
	'assert' => 4,
	'print' => 4,
	'compilestring' => 4,
	'collectgarbage' => 4,
	'type' => 4,
	'getstackinfos' => 4,
	'newthread' => 4,
*/

/*
	// デフォルト委譲
	'tointeger' => 4,
	'tofloat' => 4,
	'tostring' => 4,
	'tochar' => 4,
	'weakref' => 4,
	'len' => 4,
	'slice' => 4,
	'find' => 4,
	'tolower' => 4,
	'toupper' => 4,
	'rawget' => 4,
	'rawset' => 4,
	'rawdelete' => 4,
	'rawin' => 4,
	'append' => 4,
	'extend' => 4,
	'pop' => 4,
	'top' => 4,
	'insert' => 4,
	'remove' => 4,
	'resize' => 4,
	'sort' => 4,
	'reverse' => 4,
	'call' => 4,
	'pcall' => 4,
	'acall' => 4,
	'pacall' => 4,
	'bindenv' => 4,
	'instance' => 4,
	'getattributes' => 4,
	'getclass' => 4,
	'getstatus' => 4,
	'wakeup' => 4,
	'ref' => 4,
*/
	);

?>