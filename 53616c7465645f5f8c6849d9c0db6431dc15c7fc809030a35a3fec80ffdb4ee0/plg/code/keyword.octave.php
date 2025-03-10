<?php
/**
 * Octave キーワード定義ファイル
 */

$switchHash['.'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  //  予約語
$switchHash['$'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  //  予約語

// コメント定義
$switchHash['%'] = PLUGIN_CODE_COMMENT;    // コメントは % から改行まで
$switchHash['#'] = PLUGIN_CODE_COMMENT;	   // コメントは # から改行まで 
$code_comment = Array(
	'%' => Array(
				 Array('/^%/', "\n", 1),
		),
	'#' => Array(
				 Array('/^#/', "\n", 1),
		),
);
$outline_def = Array(
					 'begin' => Array('end', 1),
					 'function' => Array('endfunction', 1),
					 'if' => Array('endif', 1),
					 );

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
  'for'  => 2,
  'function'  => 2,
  'if'  => 2,
  'switch'  => 2,
  'try'  => 2,
  'unwind_protect'  => 2,
  'while'  => 2,
  'case'  => 2,
  'catch'  => 2,
  'else'  => 2,
  'elseif'  => 2,
  'otherwise'  => 2,
  'unwind_protect_cleanup'  => 2,
  'end'  => 2,
  'endfor'  => 2,
  'endfunction'  => 2,
  'endif'  => 2,
  'endswitch'  => 2,
  'end_try_catch'  => 2,
  'end_unwind_protect'  => 2,
  'endwhile'  => 2,
  'all_va_args'  => 2,
  'break'  => 2,
  'continue'  => 2,
  'global'  => 2,
  'gplot'  => 2,
  'gsplot'  => 2,
  'replot'  => 2,
  'return'  => 2,
  'casesen'  => 2,
  'cd'  => 2,
  'chdir'  => 2,
  'clear'  => 2,
  'diary'  => 2,
  'dir'  => 2,
  'document'  => 2,
  'echo'  => 2,
  'edit_history'  => 2,
  'format'  => 2,
  'gset'  => 2,
  'gshow'  => 2,
  'help'  => 2,
  'history'  => 2,
  'hold'  => 2,
  'load'  => 2,
  'ls'  => 2,
  'more'  => 2,
  'run_history'  => 2,
  'save'  => 2,
  'set'  => 2,
  'show'  => 2,
  'type'  => 2,
  'which'  => 2,
  'who'  => 2,
  'whos'  => 2,
  'EDITOR'  => 2,
  'EXEC_PATH'  => 2,
  'F_DUPFD'  => 2,
  'F_GETFD'  => 2,
  'F_GETFL'  => 2,
  'F_SETFD'  => 2,
  'F_SETFL'  => 2,
  'I'  => 2,
  'IMAGEPATH'  => 2,
  'INFO_FILE'  => 2,
  'INFO_PROGRAM'  => 2,
  'Inf'  => 2,
  'J'  => 2,
  'LOADPATH'  => 2,
  'NaN'  => 2,
  'OCTAVE_VERSION'  => 2,
  'O_APPEND'  => 2,
  'O_CREAT'  => 2,
  'O_EXCL'  => 2,
  'O_NONBLOCK'  => 2,
  'O_RDONLY'  => 2,
  'O_RDWR'  => 2,
  'O_TRUNC'  => 2,
  'O_WRONLY'  => 2,
  'PAGER'  => 2,
  'PS1'  => 2,
  'PS2'  => 2,
  'PS4'  => 2,
  'PWD'  => 2,
  'SEEK_CUR'  => 2,
  'SEEK_END'  => 2,
  'SEEK_SET'  => 2,
  '__F_DUPFD__'  => 2,
  '__F_GETFD__'  => 2,
  '__F_GETFL__'  => 2,
  '__F_SETFD__'  => 2,
  '__F_SETFL__'  => 2,
  '__I__'  => 2,
  '__Inf__'  => 2,
  '__J__'  => 2,
  '__NaN__'  => 2,
  '__OCTAVE_VERSION__'  => 2,
  '__O_APPEND__'  => 2,
  '__O_CREAT__'  => 2,
  '__O_EXCL__'  => 2,
  '__O_NONBLOCK__'  => 2,
  '__O_RDONLY__'  => 2,
  '__O_RDWR__'  => 2,
  '__O_TRUNC__'  => 2,
  '__O_WRONLY__'  => 2,
  '__PWD__'  => 2,
  '__SEEK_CUR__'  => 2,
  '__SEEK_END__'  => 2,
  '__SEEK_SET__'  => 2,
  '__argv__'  => 2,
  '__e__'  => 2,
  '__eps__'  => 2,
  '__error_text__'  => 2,
  '__i__'  => 2,
  '__inf__'  => 2,
  '__j__'  => 2,
  '__nan__'  => 2,
  '__pi__'  => 2,
  '__program_invocation_name__'  => 2,
  '__program_name__'  => 2,
  '__realmax__'  => 2,
  '__realmin__'  => 2,
  '__stderr__'  => 2,
  '__stdin__'  => 2,
  '__stdout__'  => 2,
  'ans'  => 2,
  'argv'  => 2,
  'automatic_replot'  => 2,
  'beep_on_error'  => 2,
  'completion_append_char'  => 2,
  'default_return_value'  => 2,
  'default_save_format'  => 2,
  'define_all_return_values'  => 2,
  'do_fortran_indexing'  => 2,
  'e'  => 2,
  'echo_executing_commands'  => 2,
  'empty_list_elements_ok'  => 2,
  'eps'  => 2,
  'error_text'  => 2,
  'gnuplot_binary'  => 2,
  'gnuplot_has_multiplot'  => 2,
  'history_file'  => 2,
  'history_size'  => 2,
  'ignore_function_time_stamp'  => 2,
  'implicit_str_to_num_ok'  => 2,
  'inf'  => 2,
  'nan'  => 2,
  'nargin'  => 2,
  'ok_to_lose_imaginary_part'  => 2,
  'output_max_field_width'  => 2,
  'output_precision'  => 2,
  'page_output_immediately'  => 2,
  'page_screen_output'  => 2,
  'pi'  => 2,
  'prefer_column_vectors'  => 2,
  'prefer_zero_one_indexing'  => 2,
  'print_answer_id_name'  => 2,
  'print_empty_dimensions'  => 2,
  'program_invocation_name'  => 2,
  'program_name'  => 2,
  'propagate_empty_matrices'  => 2,
  'realmax'  => 2,
  'realmin'  => 2,
  'resize_on_range_error'  => 2,
  'return_last_computed_value'  => 2,
  'save_precision'  => 2,
  'saving_history'  => 2,
  'silent_functions'  => 2,
  'split_long_rows'  => 2,
  'stderr'  => 2,
  'stdin'  => 2,
  'stdout'  => 2,
  'string_fill_char'  => 2,
  'struct_levels_to_print'  => 2,
  'suppress_verbose_help_message'  => 2,
  'treat_neg_dim_as_zero'  => 2,
  'warn_assign_as_truth_value'  => 2,
  'warn_comma_in_global_decl'  => 2,
  'warn_divide_by_zero'  => 2,
  'warn_function_name_clash'  => 2,
  'warn_missing_semicolon'  => 2,
  'whitespace_in_literal_matrix'  => 2,

  );
?>