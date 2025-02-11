<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Obj\Constant as ObjectConstant;
use CeusMedia\HydrogenFramework\Environment;

/** @phpstan-consistent-constructor */
class Resource_Server_Log
{
	protected Environment $env;
	protected Dictionary $moduleConfig;

	public function __construct( Environment $env ){
		$this->env			= $env;
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.server_log.', TRUE );
	}

	/**
	 *	@param		int|string				$type
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function log( int|string $type, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		if( !$this->moduleConfig->get( 'active' ) )
			return NULL;

		$format		= $format ?? strtoupper( $this->moduleConfig->get( 'format' ) );
		$context	= is_object( $context ) ? get_class( $context ) : $context;


		try{
			$logClass		= new ObjectConstant( 'Model_Log_Message' );
			$format			= $this->evaluateFormat( $logClass, $format );
			$type			= $this->evaluateType( $logClass, $type );
			$messageString	= $this->encodeMessageByFormat( $message, $format );

			$typeKey	= match( strtolower( $logClass->getKeyByValue( $type, 'TYPE' ) ) ){
				'warning'	=> 'warn',
				'notice'	=> 'note',
				default		=> $type,
			};

			if( $this->skipByType( $typeKey ) )
				return NULL;
			if( $this->skipByClientIp( $typeKey ) )
				return NULL;
			$this->saveToFile( $typeKey, $messageString, $context, $format );
			$this->saveToDatabase( $type, $messageString, $context, $format );
			return TRUE;
		}
		catch( Throwable ){
			return FALSE;
		}
	}

	/**
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function logDebug( object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return $this->log( Model_Log_Message::TYPE_DEBUG, $message, $format, $context );
	}

	/**
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function logError( object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return $this->log( Model_Log_Message::TYPE_ERROR, $message, $format, $context );
	}

	/**
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function logInfo( object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return $this->log( Model_Log_Message::TYPE_INFO, $message, $format, $context );
	}

	/**
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function logNotice( object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return $this->log( Model_Log_Message::TYPE_NOTICE, $message, $format, $context );
	}

	/**
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public function logWarning( object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return $this->log( Model_Log_Message::TYPE_WARNING, $message, $format, $context );
	}

	/**
	 *	@param		Environment				$env
	 *	@param		int|string				$type
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLog( Environment $env, int|string $type, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		$resource	= new static( $env );
		return $resource->log( $type, $message, $format, $context );
	}

	/**
	 *	@param		Environment				$env
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLogDebug( Environment $env, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return static::staticLog( $env, Model_Log_Message::TYPE_DEBUG, $message, $format, $context );
	}

	/**
	 *	@param		Environment				$env
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLogError( Environment $env, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return static::staticLog( $env, Model_Log_Message::TYPE_ERROR, $message, $format, $context );
	}

	/**
	 *	@param		Environment				$env
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLogInfo( Environment $env, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return static::staticLog( $env, Model_Log_Message::TYPE_INFO, $message, $format, $context );

	}

	/**
	 *	@param		Environment				$env
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLogNotice( Environment $env, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return static::staticLog( $env, Model_Log_Message::TYPE_NOTICE, $message, $format, $context );
	}

	/**
	 *	@param		Environment				$env
	 *	@param		object|array|string		$message
	 *	@param		object|string|NULL		$context
	 *	@param		int|string|NULL			$format
	 *	@return		bool|NULL
	 */
	public static function staticLogWarning( Environment $env, object|array|string $message, object|string|NULL $context = NULL, int|string|NULL $format = NULL ): ?bool
	{
		return static::staticLog( $env, Model_Log_Message::TYPE_WARNING, $message, $format, $context );
	}

	/**
	 *	@param		object|array|string|NULL	$message
	 *	@param		int							$format
	 *	@return		string
	 */
	protected function encodeMessageByFormat( object|array|string|NULL $message, int $format ): string
	{
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
		return $message;
	}

	/**
	 *	@param		ObjectConstant		$constants
	 *	@param		int|string			$format
	 *	@return		int
	 *	@throws		ReflectionException
	 */
	protected function evaluateFormat( ObjectConstant $constants, int|string $format ): int
	{
		/** @var array<string,int> $formats */
		$formats	= $constants->getAll( 'FORMAT' );

		if( !is_int( $format ) ){
			if( !array_key_exists( strtoupper( $format ), $formats ) )
				throw new RuntimeException( 'Invalid log message format: '.$format );
			/** @var int $format */
			$format	= $constants->getValue( strtoupper( $format ), 'FORMAT' );
		}

		if( !in_array( $format, $formats ) )
			throw new RuntimeException( 'Invalid log message format: '.$format );

		return $format;
	}

	/**
	 *	@param		ObjectConstant		$constants
	 *	@param		int|string			$type
	 *	@return		int
	 *	@throws		ReflectionException
	 */
	protected function evaluateType( ObjectConstant $constants, int|string $type ): int
	{
		$types	= $constants->getAll( 'TYPE' );
		if( !is_int( $type ) ){
			$type	= match( $type ){
				'warn'	=> 'warning',
				'note'	=> 'notice',
				default	=> $type,
			};
			if( !array_key_exists( strtoupper( $type ), $types ) )
				throw new RuntimeException( 'Invalid log message type: '.$type );
			/** @var int $type */
			$type		= $constants->getValue( strtoupper( $type ), 'TYPE' );
		}
		if( !in_array( $type, $types ) )
			throw new RuntimeException( 'Invalid log message type: '.$type );

		return $type;
	}

	/**
	 *	@param		int			$type
	 *	@param		string		$message
	 *	@param		string		$context
	 *	@param		int			$format
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function saveToDatabase( int $type, string $message, string $context, int $format ): void
	{
		$use	= $this->moduleConfig->getAll( 'use.', TRUE );
		if( isset( $this->env->dbc ) ) {
			if( $this->env->dbc ){
				$model	= new Model_Log_Message( $this->env );
				$model->add( [
					'type'				=> $type,
					'status'			=> Model_Log_Message::STATUS_NEW,
					'ip'				=> $use->get( 'ip' ) ? getEnv( 'REMOTE_ADDR' ) : NULL,
					'format'			=> $format,
					'message'			=> $message,//is_string( $message ) ? $message : json_encode( $message ),
					'userAgent'			=> $use->get( 'userAgent' ) ? getEnv( 'HTTP_USER_AGENT' ) : NULL,
					'context'			=> $context,
					'microtimestamp'	=> microtime( TRUE ),
				] );
			}
		}
	}

	/**
	 *	@param		string		$typeKey
	 *	@param		string		$message
	 *	@param		string		$context
	 *	@param		int			$format
	 *	@return		void
	 */
	protected function saveToFile( string $typeKey, string $message, string $context, int $format ): void
	{
		$use		= $this->moduleConfig->getAll( 'use.', TRUE );
		$entry		= [
			'timestamp'	=> $use->get( 'date' ) == "datestamp" ? date( "Y-m-d H:i:s" ) : time(),
			'remote_ip'	=> $use->get( 'ip' ) ? getEnv( 'REMOTE_ADDR' ) : NULL,
			'type'		=> '['.$typeKey.']',
			'context'	=> $context ? '@'.$context : NULL,
			'message'	=> $message,
			'useragent'	=> $use->get( 'userAgent' ) ? '('.getEnv( 'HTTP_USER_AGENT' ).')' : NULL,
		];
		/*  --  CLEAR PAIRS WITH EMPTY VALUES  --  */
		foreach( $entry as $key => $value )
			if( $value === NULL )
				unset( $entry[$key] );

		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$this->moduleConfig->get( 'file' );
		error_log( join( ' ', array_values( $entry ) )."\n", 3, $filePath );
	}

	/**
	 *	@param		string		$typeKey
	 *	@return		bool
	 */
	protected function skipByClientIp( string $typeKey ): bool
	{
		$ip		= getEnv( 'REMOTE_ADDR' );
		$ips	= trim( $this->moduleConfig->get( 'type.'.$typeKey.'.ips' ) );
		return strlen( $ips ) && !in_array( $ip, preg_split( '/\s*,\s*/', $ips ) );
	}

	/**
	 *	@param		string		$typeKey
	 *	@return		bool
	 */
	protected function skipByType( string $typeKey ): bool
	{
		return !$this->moduleConfig->get( 'type.'.$typeKey );
	}
}
