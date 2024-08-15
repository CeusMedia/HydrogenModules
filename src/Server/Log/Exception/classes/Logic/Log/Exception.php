<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Exception\Trace as HtmlExceptionTrace;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Log_Exception extends Logic
{
	protected string $logFile;

	protected Model_Log_Exception $model;

	protected Dictionary $moduleConfig;

	protected string $pathLogs;

	/**
	 *	@param		int|string		$id
	 *	@param		bool			$strict
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function check( int|string $id, bool $strict = TRUE ): ?object
	{
		$exception	= $this->model->get( $id );
		if( $exception )
			return $exception;
		if( $strict )
			throw new RangeException( 'Invalid exception ID' );
		return NULL;
	}

	/**
	 *	@param		Exception		$exception
	 *	@return		object
	 */
	public function collectData( Exception $exception ): object
	{
		try{
			@serialize( $exception );
			$content	= (object) [
				'exception'		=> $exception,
		//		'traceAsHtml'	=> HtmlExceptionTrace::render( $exception ),
				'trace'			=> '',
				'timestamp'		=> time(),
			];
		}
		catch( Exception ){
			$content	= (object) [
				'message'		=> $exception->getMessage(),
				'code'			=> $exception->getCode(),
				'file'			=> $exception->getFile(),
				'line'			=> $exception->getLine(),
				'trace'			=> $exception->getTraceAsString(),
				'previous'		=> $exception->getPrevious(),
				'timestamp'		=> time(),
			];
		}
		$content->env				= [
			'appName'	=> $this->env->getConfig()->get( 'app.name' ),
			'class'		=> get_class( $this->env ),
			'url'		=> $this->env->url,
			'uri'		=> $this->env->uri,
		];
		try{
			$content->request			= $this->env->getRequest();
		} catch( Exception ){}
		try{
			$sessionData	= $this->env->getSession()->getAll();
			if( isset( $sessionData['exception'] ) )
				unset( $sessionData['exception'] );
			if( isset( $sessionData['exceptionRequest'] ) )
				unset( $sessionData['exceptionRequest'] );
			if( isset( $sessionData['exceptionUrl'] ) )
				unset( $sessionData['exceptionUrl'] );
			$content->session			= $sessionData;
		} catch( Exception ){}
	//	$content->cookie			= $this->env->getCookie()->getAll();		// @todo activate for Hydrogen 0.8.6.5+
		$content->previous			= $exception->getPrevious();
		$content->class				= get_class( $exception );
		$content->classParents		= [];
		$content->classInterfaces	= [];
		$content->sqlState			= NULL;
		if( $content->class ){
			$content->classParents		= class_parents( $content->class );
			$content->classInterfaces	= class_implements( $content->class );
		}
		if( method_exists( $exception, 'getSQLSTATE' ) )
			$content->sqlState	= $exception->getSQLSTATE();

		$classes	= array_values( [$content->class] + $content->classParents );

		$content->resource		= NULL;
		if( in_array( 'Exception_IO', $classes ) )
			$content->resource		= $exception->getResource();
		$content->subject		= NULL;
		if( in_array( 'Exception_Logic', $classes ) )
			$content->subject		= $exception->getSubject();
		return $content;
	}

	/**
	 *	@param		int		$limit
	 *	@return		int
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function importFromLogFile( int $limit = 200 ): int
	{
		$count		= 0;
		if( file_exists( $this->logFile ) && filesize( $this->logFile ) !== 0 ){
			$handle		= fopen( $this->logFile, 'r' );
			while( !feof( $handle ) && $count < $limit ){
				$line	= fgets( $handle );
				if( strlen( trim( $line ) ) ){
					try{
						$this->importLogFileItem( $line );
					}
					catch( Exception ){}
					$count++;
				}
			}
			if( $count !== 0 ){
				// @link https://www.baeldung.com/linux/remove-first-line-text-file
				$command	= 'tail -n +%1$d %2$s > %2$s.tmp && mv %2$s.tmp %2$s';
				exec( sprintf( $command, $count + 1, $this->logFile ) );
			}
		}
		return $count;
	}

	/**
	 *	@param		string		$line
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function importLogFileItem( string $line ): string
	{
		[$timestamp, $dataEncoded]	= explode( ":", $line );
		$data	= base64_decode( $dataEncoded );
		$object	= @unserialize( $data );
		if( !is_object( $object ) )
			throw new InvalidArgumentException( "Line is not containing an exception data object" );

		$data	= [
			'status'		=> 0,
			'message'		=> '',
			'trace'			=> '',
			'createdAt'		=> $timestamp,
			'modifiedAt'	=> time(),
		];

		if( isset( $object->exception ) && $object->exception instanceof Exception ){
			$data	= array_merge( $data, [
				'type'			=> get_class( $object->exception ),
				'message'		=> $object->exception->getMessage(),
				'code'			=> $object->exception->getCode(),
				'file'			=> $object->exception->getFile(),
				'line'			=> $object->exception->getLine(),
				'trace'			=> $object->exception->getTraceAsString(),
				'previous'		=> serialize( $object->exception->getPrevious() ),
			] );
		}

		else if( $object instanceof Exception ){
			$data	= array_merge( $data, [
				'type'			=> get_class( $object ),
				'message'		=> $object->getMessage(),
				'code'			=> $object->getCode(),
				'file'			=> $object->getFile(),
				'line'			=> $object->getLine(),
				'trace'			=> $object->getTraceAsString(),
				'previous'		=> serialize( $object->getPrevious() ),
			] );
		}
		else{
			$data	= array_merge( $data, [
				'type'			=> 'Exception',
				'message'		=> $object->message,
				'code'			=> $object->code,
				'file'			=> $object->file,
				'line'			=> $object->line,
				'trace'			=> $object->trace,
			] );
		}
		if( empty( $data['trace'] ) ){
			print_m( $object );die;
		}
		$data['env']	= '';
		if( !empty( $object->env ) )
			$data['env']	= serialize( $object->env );
		if( property_exists( $object, 'request' ) && !empty( $object->request ) )
			$data['request']	= serialize( $object->request );
		if( property_exists( $object, 'session' ) && !empty( $object->session ) )
			$data['session']	= serialize( $object->session );
		return $this->model->add( $data, FALSE );
	}

	/**
	 *	@param		Exception		$exception
	 *	@return		bool|NULL
	 *	@throws		ReflectionException
	 */
	public function log( Exception $exception ): ?bool
	{
		$payload	= ['exception' => $exception];
		return $this->captain->callHook( 'Env', 'logException', $this->env, $payload );
	}

	/**
	 *	@param		$data
	 *	@return		void
	 */
	public function saveCollectedDataToLogFile( $data ): void
	{
		if( $this->moduleConfig->get( 'file.active' ) ){
			if( trim( $this->moduleConfig->get( 'file.name' ) ) ){
				$msg	= time().":".base64_encode( serialize( $data ) );
//				$msg	= (new DateTime())->format( 'Y-m-d H:i:s' ).": ".json_encode( $data, JSON_PRETTY_PRINT );
				error_log( $msg.PHP_EOL, 3, $this->logFile );
			}
		}
	}

	/**
	 *	@param		$data
	 *	@return		FALSE|void
	 *	@todo		implement
	 */
	public function sendCollectedDataAsMail( $data )
	{
		if( !$this->moduleConfig->get( 'mail.active' ) )
			return FALSE;
		die( 'Not implemented, yet.' );
	}

	/**
	 *	@param		Exception		$exception
	 *	@return		FALSE|void
	 *	@throws		ReflectionException
	 */
	public function sendExceptionAsMail( Exception $exception )
	{
		if( !$this->moduleConfig->get( 'mail.active' ) )
			return FALSE;
		$hasReceivers	= trim( $this->moduleConfig->get( 'mail.receivers' ) );
		$hasMailModule	= $this->env->getModules()->has( 'Resource_Mail' );
		$hasBaseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		if( !( $hasReceivers && $hasMailModule && $hasBaseUrl ) )
			return FALSE;
		$language		= $this->env->getLanguage()->getLanguage();
		$logicMail		= Logic_Mail::getInstance( $this->env );
		$mail			= new Mail_Log_Exception( $this->env, ['exception' => $exception] );
		$receivers		= preg_split( '/(,|;)/', $this->moduleConfig->get( 'mail.receivers' ) );
		foreach( $receivers as $receiver ){
			if( trim( $receiver ) ){
				$receiver	= (object) ['email' => $receiver];
				$logicMail->handleMail( $mail, $receiver, $language );
			}
		}
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_Log_Exception( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$this->pathLogs		= $this->env->getConfig()->get( 'path.logs' );
		if( $this->env->getModules()->has( 'Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$this->pathLogs		= $frontend->getPath( 'logs' );
			$moduleConfig		= $frontend->getModuleConfigValues( 'Server_Log_Exception' );
			$this->moduleConfig	= new Dictionary( $moduleConfig );
		}
		$this->logFile		= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
	}
}
