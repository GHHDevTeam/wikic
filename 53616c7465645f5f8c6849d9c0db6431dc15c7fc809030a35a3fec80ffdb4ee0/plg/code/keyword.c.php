<?php
/**
 * C/C++ キーワード定義ファイル
 *
 * Modified:   <12/09/27 ruche>
 */

$switchHash['#'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  // # から始まる予約語あり
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

$outline_def = Array(
	'#region' => Array('#endregion', 0),
	);

$code_css = Array(
	'operator',		// オペレータ関数
	'identifier',	// その他の識別子
	'pragma',		// プリプロセッサ
	'system',		// 組み込みマクロ
	'outline',
	);

/// HACK: 2014-01-11 add by ruche ->

// スペースを含むキーワード
$code_space_keyword = Array(
	'enum class' => 2,
	'enum struct' => 2,
	'long long int' => 2,
	'long long' => 2,
	'long int' => 2,
	'short int' => 2,
	);

/// <- 2014-01-11 add by ruche

$code_keyword = Array(
	'operator' => 1,

	'asm' => 2,
	'auto' => 2,
	'extern' => 2,
	'inline' => 2,
	'private' => 2,
	'protected' => 2,
	'public' => 2,
	'register' => 2,
	'virtual' => 2,

	'if' => 2,
	'for' => 2,
	'goto' => 2,
	'switch' => 2,
	'while' => 2,
	'do' => 2,
	'endif' => 2,
	'else' => 2,
	'case' => 2,
	'default' => 2,
	'break' => 2,
	'continue' => 2,
	'return' => 2,

	'const' => 2,
	'static' => 2,
	'friend' => 2,
	'false' => 2,
	'true' => 2,

	'signed' => 2,
	'unsigned' => 2,
	'void' => 2,
	'bool' => 2,
	'char' => 2,
	'short' => 2,
	'int' => 2,
	'long' => 2,
	'float' => 2,
	'double' => 2,
	'wchar_t' => 2,

	'this' => 2,

	'sizeof' => 2,

	'enum' => 2,
	'struct' => 2,
	'union' => 2,
	'class' => 2,

	'delete' => 2,
	'new' => 2,

	'try' => 2,
	'catch' => 2,
	'throw' => 2,
	'explicit' => 2,
	'mutable' => 2,
	'template' => 2,
	'volatile' => 2,

	'#define' => 3,
	'#elif' => 3,
	'#else' => 3,
	'#endif' => 3,
	'#error' => 3,
	'#if' => 3,
	'#ifdef' => 3,
	'#ifndef' => 3,
	'#include' => 3,
	'#line' => 3,
	'#pragma' => 3,
	'#undef' => 3,

	/// HACK: 2009-11-20 modify by ruche ->

	'typedef' => 2, // 3,
	'typename' => 2, // 3,
	'namespace' => 2, // 3,
	'using' => 2, // 3,

	/// <- 2009-11-20 modify by ruche

	'__declspec' => 2,
	'__stdcall' => 2,
	'__fastcall' => 2,

	'__FILE__' => 4,
	'__LINE__' => 4,

	'#region' => 5,
	'#endregion' => 5,

	/// HACK: 2009-11-09 add by ruche ->

	// C++ cast operators
	'static_cast' => 2,
	'const_cast' => 2,
	'dynamic_cast' => 2,
	'reinterpret_cast' => 2,

	// C macros
	'__DATE__' => 4,
	'__TIME__' => 4,
	'__func__' => 4,

	// Visual Studio keywords
	'__int8' => 2,
	'__int16' => 2,
	'__int32' => 2,
	'__int64' => 2,
	'__int128' => 2,
	'_declspec' => 2,
	'_stdcall' => 2,
	'_fastcall' => 2,
	'_asm' => 2,
	'__asm' => 2,

	/// <- 2009-11-09 add by ruche

	/// HACK: 2012-09-27 add by ruche ->

	// C++11 keywords
	'alignas' => 2,
	'alignof' => 2,
	'char16_t' => 2,
	'char32_t' => 2,
	'constexpr' => 2,
	'decltype' => 2,
	'noexcept' => 2,
	'nullptr' => 2,
	'static_assert' => 2,
	'thread_local' => 2,

	/// <- 2012-09-27 add by ruche

	/// HACK: 2014-01-11 add by ruche ->

	// C++ macro
	'__cplusplus' => 4,

	// C++11 keywords
	'override' => 2,
	'final' => 2,

	/// <- 2014-01-11 add by ruche
	);

?>