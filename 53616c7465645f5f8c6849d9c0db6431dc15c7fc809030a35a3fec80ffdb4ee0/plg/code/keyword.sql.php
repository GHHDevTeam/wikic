<?php
/**
 * SQL キーワード定義ファイル
 */

$switchHash['\`'] = PLUGIN_CODE_STRING_LITERAL;  // ` も文字列リテラル
$capital = 1;                    // 予約語の大文字小文字を区別しない

// コメント定義
$switchHash['-'] = PLUGIN_CODE_COMMENT;    // コメントは -- から改行まで
$switchHash['/'] = PLUGIN_CODE_COMMENT;    // コメントは /* から */ まで
$code_comment = Array(
 	'-' => Array(
				 Array('/^--/', "\n", 1),
	),
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
 	)
 );

// アウトライン用
if($mkoutline){
  $switchHash['('] = PLUGIN_CODE_BLOCK_START;
  $switchHash[')'] = PLUGIN_CODE_BLOCK_END;
}

$outline_def = Array(
					 'begin' => Array('end', 1),
					 );

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
	'abort'=> 2,
	'abs'=> 2,
	'absolute'=> 2,
	'access'=> 2,
	'action'=> 2,
	'ada'=> 2,
	'add'=> 2,
	'admin'=> 2,
	'after'=> 2,
	'aggregate'=> 2,
	'alias'=> 2,
	'all'=> 2,
	'allocate'=> 2,
	'alter'=> 2,
	'analyse'=> 2,
	'analyze'=> 2,
	'and'=> 2,
	'any'=> 2,
	'are'=> 2,
	'array'=> 2,
	'as'=> 2,
	'asc'=> 2,
	'asensitive'=> 2,
	'assertion'=> 2,
	'assignment'=> 2,
	'asymmetric'=> 2,
	'at'=> 2,
	'atomic'=> 2,
	'authorization'=> 2,
	'avg'=> 2,
	'backward'=> 2,
	'before'=> 2,
	'begin'=> 2,
	'between'=> 2,
	'bigint'=> 2,
	'binary'=> 2,
	'bit'=> 2,
	'bitvar'=> 2,
	'bit_length'=> 2,
	'blob'=> 2,
	'boolean'=> 2,
	'both'=> 2,
	'breadth'=> 2,
	'by'=> 2,
	'c'=> 2,
	'cache'=> 2,
	'call'=> 2,
	'called'=> 2,
	'cardinality'=> 2,
	'cascade'=> 2,
	'cascaded'=> 2,
	'case'=> 2,
	'cast'=> 2,
	'catalog'=> 2,
	'catalog_name'=> 2,
	'chain'=> 2,
	'char'=> 2,
	'character'=> 2,
	'characteristics'=> 2,
	'character_length'=> 2,
	'character_set_catalog'=> 2,
	'character_set_name'=> 2,
	'character_set_schema'=> 2,
	'char_length'=> 2,
	'check'=> 2,
	'checked'=> 2,
	'checkpoint'=> 2,
	 /*'class'=> 2, */
    'class_origin'=> 2,
	'clob'=> 2,
	'close'=> 2,
	'cluster'=> 2,
	'coalesce'=> 2,
	'cobol'=> 2,
	'collate'=> 2,
	'collation'=> 2,
	'collation_catalog'=> 2,
	'collation_name'=> 2,
	'collation_schema'=> 2,
	'column'=> 2,
	'column_name'=> 2,
	'command_function'=> 2,
	'command_function_code'=> 2,
	'comment'=> 2,
	'commit'=> 2,
	'committed'=> 2,
	'completion'=> 2,
	'condition_number'=> 2,
	'connect'=> 2,
	'connection'=> 2,
	'connection_name'=> 2,
	'constraint'=> 2,
	'constraints'=> 2,
	'constraint_catalog'=> 2,
	'constraint_name'=> 2,
	'constraint_schema'=> 2,
	'constructor'=> 2,
	'contains'=> 2,
	'continue'=> 2,
	'conversion'=> 2,
	'convert'=> 2,
	'copy'=> 2,
	'corresponding'=> 2,
	'count'=> 2,
	'create'=> 2,
	'createdb'=> 2,
	'createuser'=> 2,
	'cross'=> 2,
	'cube'=> 2,
	'current'=> 2,
	'current_date'=> 2,
	'current_path'=> 2,
	'current_role'=> 2,
	'current_time'=> 2,
	'current_timestamp'=> 2,
	'current_user'=> 2,
	'cursor'=> 2,
	'cursor_name'=> 2,
	'cycle'=> 2,
	'data'=> 2,
	'database'=> 2,
	'date'=> 2,
	'datetime_interval_code'=> 2,
	'datetime_interval_precision'=> 2,
	'day'=> 2,
	'deallocate'=> 2,
	'dec'=> 2,
	'decimal'=> 2,
	'declare'=> 2,
	'default'=> 2,
	'defaults'=> 2,
	'deferrable'=> 2,
	'deferred'=> 2,
	'defined'=> 2,
	'definer'=> 2,
	'delete'=> 2,
	'delimiter'=> 2,
	'delimiters'=> 2,
	'depth'=> 2,
	'deref'=> 2,
	'desc'=> 2,
	'describe'=> 2,
	'descriptor'=> 2,
	'destroy'=> 2,
	'destructor'=> 2,
	'deterministic'=> 2,
	'diagnostics'=> 2,
	'dictionary'=> 2,
	'disconnect'=> 2,
	'dispatch'=> 2,
	'distinct'=> 2,
	'do'=> 2,
	'domain'=> 2,
	'double'=> 2,
	'drop'=> 2,
	'dynamic'=> 2,
	'dynamic_function'=> 2,
	'dynamic_function_code'=> 2,
	'each'=> 2,
	'else'=> 2,
	'encoding'=> 2,
	'encrypted'=> 2,
	'end'=> 2,
	'end-exec'=> 2,
	'equals'=> 2,
	'escape'=> 2,
	'every'=> 2,
	'except'=> 2,
	'exception'=> 2,
	'excluding'=> 2,
	'exclusive'=> 2,
	'exec'=> 2,
	'execute'=> 2,
	'existing'=> 2,
	'exists'=> 2,
	'explain'=> 2,
	'external'=> 2,
	'extract'=> 2,
	'false'=> 2,
	'fetch'=> 2,
	'final'=> 2,
	'first'=> 2,
	'float'=> 2,
	'for'=> 2,
	'force'=> 2,
	'foreign'=> 2,
	'fortran'=> 2,
	'forward'=> 2,
	'found'=> 2,
	'free'=> 2,
	'freeze'=> 2,
	'from'=> 2,
	'full'=> 2,
	'function'=> 2,
	'g'=> 2,
	'general'=> 2,
	'generated'=> 2,
	'get'=> 2,
	'global'=> 2,
	'go'=> 2,
	'goto'=> 2,
	'grant'=> 2,
	'granted'=> 2,
	'group'=> 2,
	'grouping'=> 2,
	'handler'=> 2,
	'having'=> 2,
	'hierarchy'=> 2,
	'hold'=> 2,
	'host'=> 2,
	'hour'=> 2,
	'identity'=> 2,
	'ignore'=> 2,
	'ilike'=> 2,
	'immediate'=> 2,
	'immutable'=> 2,
	'implementation'=> 2,
	'implicit'=> 2,
	'in'=> 2,
	'including'=> 2,
	'increment'=> 2,
	'index'=> 2,
	'indicator'=> 2,
	'infix'=> 2,
	'inherits'=> 2,
	'initialize'=> 2,
	'initially'=> 2,
	'inner'=> 2,
	'inout'=> 2,
	'input'=> 2,
	'insensitive'=> 2,
	'insert'=> 2,
	'instance'=> 2,
	'instantiable'=> 2,
	'instead'=> 2,
	'int'=> 2,
	'integer'=> 2,
	'intersect'=> 2,
	'interval'=> 2,
	'into'=> 2,
	'invoker'=> 2,
	'is'=> 2,
	'isnull'=> 2,
	'isolation'=> 2,
	'iterate'=> 2,
	'join'=> 2,
	'k'=> 2,
	'key'=> 2,
	'key_member'=> 2,
	'key_type'=> 2,
	'lancompiler'=> 2,
	'language'=> 2,
	'large'=> 2,
	'last'=> 2,
	'lateral'=> 2,
	'leading'=> 2,
	'left'=> 2,
	'length'=> 2,
	'less'=> 2,
	'level'=> 2,
	'like'=> 2,
	'limit'=> 2,
	'listen'=> 2,
	'load'=> 2,
	'local'=> 2,
	'localtime'=> 2,
	'localtimestamp'=> 2,
	'location'=> 2,
	'locator'=> 2,
	'lock'=> 2,
	'lower'=> 2,
	'm'=> 2,
	'map'=> 2,
	'match'=> 2,
	'max'=> 2,
	'maxvalue'=> 2,
	'message_length'=> 2,
	'message_octet_length'=> 2,
	'message_text'=> 2,
	'method'=> 2,
	'min'=> 2,
	'minute'=> 2,
	'minvalue'=> 2,
	'mod'=> 2,
	'mode'=> 2,
	'modifies'=> 2,
	'modify'=> 2,
	'module'=> 2,
	'month'=> 2,
	'more'=> 2,
	'move'=> 2,
	'mumps'=> 2,
	'name'=> 2,
	'names'=> 2,
	'national'=> 2,
	'natural'=> 2,
	'nchar'=> 2,
	'nclob'=> 2,
	'new'=> 2,
	'next'=> 2,
	'no'=> 2,
	'nocreatedb'=> 2,
	'nocreateuser'=> 2,
	'none'=> 2,
	'not'=> 2,
	'nothing'=> 2,
	'notify'=> 2,
	'notnull'=> 2,
	'null'=> 2,
	'nullable'=> 2,
	'nullif'=> 2,
	'number'=> 2,
	'numeric'=> 2,
	'object'=> 2,
	'octet_length'=> 2,
	'of'=> 2,
	'off'=> 2,
	'offset'=> 2,
	'oids'=> 2,
	'old'=> 2,
	'on'=> 2,
	'only'=> 2,
	'open'=> 2,
	'operation'=> 2,
	'operator'=> 2,
	'option'=> 2,
	'options'=> 2,
	'or'=> 2,
	'order'=> 2,
	'ordinality'=> 2,
	'out'=> 2,
	'outer'=> 2,
	'output'=> 2,
	'overlaps'=> 2,
	'overlay'=> 2,
	'overriding'=> 2,
	'owner'=> 2,
	'pad'=> 2,
	'parameter'=> 2,
	'parameters'=> 2,
	'parameter_mode'=> 2,
	'parameter_name'=> 2,
	'parameter_ordinal_position'=> 2,
	'parameter_specific_catalog'=> 2,
	'parameter_specific_name'=> 2,
	'parameter_specific_schema'=> 2,
	'partial'=> 2,
	'pascal'=> 2,
	'password'=> 2,
	'path'=> 2,
	'pendant'=> 2,
	'placing'=> 2,
	'pli'=> 2,
	'position'=> 2,
	'postfix'=> 2,
	'precision'=> 2,
	'prefix'=> 2,
	'preorder'=> 2,
	'prepare'=> 2,
	'preserve'=> 2,
	'primary'=> 2,
	'prior'=> 2,
	'privileges'=> 2,
	'procedural'=> 2,
	'procedure'=> 2,
	'public'=> 2,
	'read'=> 2,
	'reads'=> 2,
	'real'=> 2,
	'recheck'=> 2,
	'recursive'=> 2,
	'ref'=> 2,
	'references'=> 2,
	'referencing'=> 2,
	'reindex'=> 2,
	'relative'=> 2,
	'rename'=> 2,
	'repeatable'=> 2,
	'replace'=> 2,
	'reset'=> 2,
	'restart'=> 2,
	'restrict'=> 2,
	'result'=> 2,
	'return'=> 2,
	'returned_length'=> 2,
	'returned_octet_length'=> 2,
	'returned_sqlstate'=> 2,
	'returns'=> 2,
	'revoke'=> 2,
	'right'=> 2,
	'role'=> 2,
	'rollback'=> 2,
	'rollup'=> 2,
	'routine'=> 2,
	'routine_catalog'=> 2,
	'routine_name'=> 2,
	'routine_schema'=> 2,
	'row'=> 2,
	'rows'=> 2,
	'row_count'=> 2,
	'rule'=> 2,
	'savepoint'=> 2,
	'scale'=> 2,
	'schema'=> 2,
	'schema_name'=> 2,
	'scope'=> 2,
	'scroll'=> 2,
	'search'=> 2,
	'second'=> 2,
	'section'=> 2,
	'security'=> 2,
	'select'=> 2,
	'self'=> 2,
	'sensitive'=> 2,
	'sequence'=> 2,
	'serializable'=> 2,
	'server_name'=> 2,
	'session'=> 2,
	'session_user'=> 2,
	'set'=> 2,
	'setof'=> 2,
	'sets'=> 2,
	'share'=> 2,
	'show'=> 2,
	'similar'=> 2,
	'simple'=> 2,
	'size'=> 2,
	'smallint'=> 2,
	'some'=> 2,
	'source'=> 2,
	'space'=> 2,
	'specific'=> 2,
	'specifictype'=> 2,
	'specific_name'=> 2,
	'sql'=> 2,
	'sqlcode'=> 2,
	'sqlerror'=> 2,
	'sqlexception'=> 2,
	'sqlstate'=> 2,
	'sqlwarning'=> 2,
	'stable'=> 2,
	'start'=> 2,
	'state'=> 2,
	'statement'=> 2,
	'static'=> 2,
	'statistics'=> 2,
	'stdin'=> 2,
	'stdout'=> 2,
	'storage'=> 2,
	'strict'=> 2,
	'structure'=> 2,
	'style'=> 2,
	'subclass_origin'=> 2,
	'sublist'=> 2,
	'substring'=> 2,
	'sum'=> 2,
	'symmetric'=> 2,
	'sysid'=> 2,
	'system'=> 2,
	'system_user'=> 2,
	'table'=> 2,
	'table_name'=> 2,
	'temp'=> 2,
	'template'=> 2,
	'temporary'=> 2,
	'terminate'=> 2,
	'text'=> 2,
	'than'=> 2,
	'then'=> 2,
	'time'=> 2,
	'timestamp'=> 2,
	'timezone_hour'=> 2,
	'timezone_minute'=> 2,
	'to'=> 2,
	'toast'=> 2,
	'trailing'=> 2,
	'transaction'=> 2,
	'transactions_committed'=> 2,
	'transactions_rolled_back'=> 2,
	'transaction_active'=> 2,
	'transform'=> 2,
	'transforms'=> 2,
	'translate'=> 2,
	'translation'=> 2,
	'treat'=> 2,
	'trigger'=> 2,
	'trigger_catalog'=> 2,
	'trigger_name'=> 2,
	'trigger_schema'=> 2,
	'trim'=> 2,
	'true'=> 2,
	'truncate'=> 2,
	'trusted'=> 2,
	'type'=> 2,
	'uncommitted'=> 2,
	'under'=> 2,
	'unencrypted'=> 2,
	'union'=> 2,
	'unique'=> 2,
	'unknown'=> 2,
	'unlisten'=> 2,
	'unnamed'=> 2,
	'unnest'=> 2,
	'until'=> 2,
	'update'=> 2,
	'upper'=> 2,
	'usage'=> 2,
	'user'=> 2,
	'user_defined_type_catalog'=> 2,
	'user_defined_type_name'=> 2,
	'user_defined_type_schema'=> 2,
	'using'=> 2,
	'vacuum'=> 2,
	'valid'=> 2,
	'validator'=> 2,
	'value'=> 2,
	'values'=> 2,
	'varchar'=> 2,
	'variable'=> 2,
	'varying'=> 2,
	'verbose'=> 2,
	'version'=> 2,
	'view'=> 2,
	'volatile'=> 2,
	'when'=> 2,
	'whenever'=> 2,
	'where'=> 2,
	'with'=> 2,
	'without'=> 2,
	'work'=> 2,
	'write'=> 2,
	'year'=> 2,
	'zone'=> 2,
  );
?>
