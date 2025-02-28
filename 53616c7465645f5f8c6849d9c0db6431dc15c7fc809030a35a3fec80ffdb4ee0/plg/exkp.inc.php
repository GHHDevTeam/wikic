<?php
function plugin_exkp_convert()
{ 
	global $agent;
	$args = func_get_args();
	if( in_array( 'p', $args ) ){
		if( $agent[ 'profile' ] != 'default' )
		{
			$source = array();
			$source = explode( "\r",$args[ 1 ] );
			return convert_html( $source );
		}
	}else{
		if( $agent[ 'profile' ] != 'keitai' )
		{
			$source = array();
			$source = explode( "\r",$args[ 0 ] );
			return convert_html( $source );
		}
	}
} 
?> 