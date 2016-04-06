<?php
class Controller_Server_Log extends CMF_Hydrogen_Controller{

	static public function ___onEnvLog( $env, $context, $module, $data ){
		$options	= $env->getConfig()->getAll( 'module.server_log.', TRUE );

		if( !$options->get( 'active' ) )
			return;
		if( !$options->get( 'type.'.$data['type'] ) )
			return;

		$ip		= getEnv( 'REMOTE_ADDR' );
		$ips	= trim( $options->get( 'type.'.$data['type'].'.ips' ) );
		if( strlen( $ips ) && !in_array( $ip, explode( ",", $ips ) ) )
			return;

		$use		= $options->getAll( 'use.', TRUE );
		$entry		= array(
			'at'	=> $use->get( 'date' ) == "datestamp" ? date( "Y-m-d H:i:s" ) : time(),
			'ip'	=> $use->get( 'ip' ) ? getEnv( 'REMOTE_ADDR' ) : NULL,
			'type'	=> $data['type'],
			'msg'	=> $data['message'],
			'ua'	=> $use->get( 'userAgent' ) ? getEnv( 'HTTP_USER_AGENT' ) : NULL,
		);
		foreach( $entry as $key => $value )
			if( $value === NULL )
				unset( $entry[$key] );

		$filePath	= $env->getConfig()->get( 'path.logs' ).$options->get( 'file' );
		switch( strtoupper( $options->get( 'format' ) ) ){
			case 'JSON':
				$content	= json_encode( $entry );
				break;
			case 'PHP':
				$content	= serialize( $entry );
				break;
			default:
				$content	= join( " ", array_values( $entry ) );
		}
		error_log( $content."\n", 3, $filePath );
		return TRUE;
	}
}
?>
