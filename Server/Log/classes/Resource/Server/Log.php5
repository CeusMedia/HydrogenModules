<?php
class Resource_Server_Log{

	protected $env;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function log( $type, $message, $context = NULL, $format = NULL ){
		$options	= $this->env->getConfig()->getAll( 'module.server_log.', TRUE );
		if( !$options->get( 'active' ) )
			return NULL;

		$logClass	= new Alg_Object_Constant( 'Model_Log_Message' );
		$types		= $logClass->getAll( 'TYPE' );
		$formats	= $logClass->getAll( 'FORMAT' );

		if( is_int( $type ) ){
			if( !in_array( $type, $types ) )
				throw new RuntimeException( 'Invalid log message type: '.$type );
		}
		else{
			if( !array_key_exists( strtoupper( $type ), $types ) )
				throw new RuntimeException( 'Invalid log message type: '.$type );
			$type		= $logClass->getValue( strtoupper( $type ), 'TYPE' );
		}
		$typeKey	= strtolower( $logClass->getKeyByValue( $type, 'TYPE' ) );

		if( is_null( $format ) )
			$format	= strtoupper( $options->get( 'format' ) );
		else if( is_int( $format ) ){
			if( !in_array( $format, $formats ) )
				throw new RuntimeException( 'Invalid log message format: '.$format );
		}
		else{
			if( !array_key_exists( strtoupper( $format ), $formats ) )
				throw new RuntimeException( 'Invalid log message format: '.$format );
			$format	= $logClass->getValue( strtoupper( $format ), 'FORMAT' );
		}

		if( !$options->get( 'type.'.$typeKey ) )
			return NULL;

		$ip		= getEnv( 'REMOTE_ADDR' );
		$ips	= trim( $options->get( 'type.'.$typeKey.'.ips' ) );
		if( strlen( $ips ) && !in_array( $ip, explode( ",", $ips ) ) )
			return NULL;

		$use		= $options->getAll( 'use.', TRUE );
		switch( $format ){
			case Model_Log_Message::FORMAT_PHP:
				$message	= serialize( $message );
				break;
			case Model_Log_Message::FORMAT_TEXT:
				if( is_object( $message ) || is_array( $message ) ){
					$message	= print_m( $message, NULL, NULL, TRUE );
				}
				break;
			case Model_Log_Message::FORMAT_JSON:
			default:
				$message	= json_encode( $message );
				break;
		}

		/*  --  FILE STORAGE  --  */
		$context	= is_object( $context ) ? get_class( $context ) : $context;
		$entry		= array(
			'timestamp'	=> $use->get( 'date' ) == "datestamp" ? date( "Y-m-d H:i:s" ) : time(),
			'remote_ip'	=> $use->get( 'ip' ) ? getEnv( 'REMOTE_ADDR' ) : NULL,
			'type'		=> '['.$typeKey.']',
			'context'	=> $context ? '@'.$context : NULL,
			'message'	=> $message,
			'useragent'	=> $use->get( 'userAgent' ) ? '('.getEnv( 'HTTP_USER_AGENT' ).')' : NULL,
		);
		/*  --  CLEAR PAIRS WITH EMPTY VALUES  --  */
		foreach( $entry as $key => $value )
			if( $value === NULL )
				unset( $entry[$key] );
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$options->get( 'file' );
		error_log( join( ' ', array_values( $entry ) )."\n", 3, $filePath );

		/*  --  DATABASE STORAGE  --  */
		if( $this->env->dbc ){
			$model	= new Model_Log_Message( $this->env );
			$model->add( array(
				'type'				=> $type,
				'status'			=> Model_Log_Message::STATUS_NEW,
				'ip'				=> $use->get( 'ip' ) ? getEnv( 'REMOTE_ADDR' ) : NULL,
				'format'			=> $format,
				'message'			=> is_string( $message ) ? $message : json_encode( $message ),
				'userAgent'			=> $use->get( 'userAgent' ) ? getEnv( 'HTTP_USER_AGENT' ) : NULL,
				'context'			=> $context,
				'microtimestamp'	=> microtime( TRUE ),
			) );
		}
		return TRUE;
	}

	public function logDebug( $message, $context = NULL, $format = NULL ){
		return $this->log( Model_Log_Message::TYPE_DEBUG, $message, $format, $context );
	}

	public function logError( $message, $context = NULL, $format = NULL ){
		return $this->log( Model_Log_Message::TYPE_ERROR, $message, $format, $context );
	}

	public function logInfo( $message, $context = NULL, $format = NULL ){
		return $this->log( Model_Log_Message::TYPE_INFO, $message, $format, $context );
	}

	public function logNotice( $message, $context = NULL, $format = NULL ){
		return $this->log( Model_Log_Message::TYPE_NOTICE, $message, $format, $context );
	}

	public function logWarning( $message, $context = NULL, $format = NULL ){
		return $this->log( Model_Log_Message::TYPE_WARNING, $message, $format, $context );
	}

	static public function staticLog( $env, $type, $message, $context = NULL, $format = NULL ){
		$resource	= new static( $env );
		return $resource->log( $type, $message, $format, $context );
	}

	static public function staticLogDebug( $env, $message, $context = NULL, $format = NULL ){
		return static::staticLog( $env, Model_Log_Message::TYPE_DEBUG, $message, $format, $context );
	}

	static public function staticLogError( $env, $message, $context = NULL, $format = NULL ){
		return static::staticLog( $env, Model_Log_Message::TYPE_ERROR, $message, $format, $context );
	}

	static public function staticLogInfo( $env, $message, $context = NULL, $format = NULL ){
		return static::staticLog( $env, Model_Log_Message::TYPE_INFO, $message, $format, $context );

	}
	static public function staticLogNotice( $env, $message, $context = NULL, $format = NULL ){
		return static::staticLog( $env, Model_Log_Message::TYPE_NOTICE, $message, $format, $context );
	}

	static public function staticLogWarning( $env, $message, $context = NULL, $format = NULL ){
		return static::staticLog( $env, Model_Log_Message::TYPE_WARNING, $message, $format, $context );
	}
}
?>
