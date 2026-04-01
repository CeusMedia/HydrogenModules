<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
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
	 *	@param		Throwable		$exception
	 *	@return		object
	 */
	public function collectData( Throwable $exception ): object
	{
		$content	= Entity_Log_Exception_FileLogEntry::fromArray( [
			'class'				=> get_class( $exception ),
			'classParents'		=> class_parents( get_class( $exception ) ),
			'classInterfaces'	=> class_implements( get_class( $exception ) ),
			'env'	=> [
				'appName'	=> $this->env->getConfig()->get( 'app.name' ),
				'class'		=> get_class( $this->env ),
				'url'		=> $this->env->url,
				'uri'		=> $this->env->uri,
			],
			'timestamp'		=> time(),
		] );
		try{
			@serialize( $exception );
			$content->exception	= $exception;
		}
		catch( Throwable ){
			$content->message	= $exception->getMessage();
			$content->code		= $exception->getCode();
			$content->file		= $exception->getFile();
			$content->line		= $exception->getLine();
			$content->trace		= $exception->getTraceAsString();
			$content->previous	= $exception->getPrevious();
		}

		if( $this->env->getModules()->has( 'Server_Log_Request' ) ){
			$singletonRequestLogLogic	= Logic_Server_Log_Request::getInstance( $this->env );
			$content->requestId			= (int) $singletonRequestLogLogic->getCurrentRequestId();
		}
		else{
			try{
				$content->request			= $this->env->getRequest();
			} catch( Throwable ){}
			try{
				$sessionData	= $this->env->getSession()->getAll( NULL, TRUE );
				$sessionData->remove( 'exception' );
				$sessionData->remove( 'exceptionRequest' );
				$sessionData->remove( 'exceptionUrl' );
				$content->session	= $sessionData->getAll( NULL, TRUE );
			} catch( Throwable ){}
		//	$content->cookie			= $this->env->getCookie()->getAll();		// @todo activate for Hydrogen 0.8.6.5+
		}

		if( method_exists( $exception, 'getSQLSTATE' ) )
			$content->sqlState	= $exception->getSQLSTATE();

		$classes	= array_values( [$content->class] + $content->classParents );
		if( in_array( 'CeusMedia\Common\Exception\IO', $classes ) )
			$content->resource		= $exception->getResource();
		if( in_array( 'CeusMedia\Common\Exception\Logic', $classes ) )
			$content->subject		= $exception->getSubject();

		return $content;
	}

	/**
	 *	@param		int			$limit			Number of lines to import at once, default: 200
	 *	@param		string		$strategy		One of 'recreatingUsingTailStrategy', 'strictInlineStrategy'
	 *	@return		int
	 */
	public function importFromLogFile( int $limit = 200, string $strategy = 'recreatingUsingTailStrategy' ): int
	{
		return match( $strategy ){
			'strictInlineStrategy'	=> $this->importFromLogFileByStrictInlineStrategy( $limit ),
			default					=> $this->importFromLogFileByRecreatingUsingTailStrategy( $limit ),
		};
	}

	/**
	 *	@param		string		$line
	 *	@return		int|string
	 */
	public function importLogFileItem( string $line ): int|string
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

		if( isset( $object->exception ) && $object->exception instanceof Throwable ){
			$data	= array_merge( $data, [
				'type'			=> get_class( $object->exception ),
				'message'		=> $object->exception->getMessage(),
				'code'			=> $object->exception->getCode() ?? '',
				'file'			=> $object->exception->getFile(),
				'line'			=> $object->exception->getLine(),
				'trace'			=> $object->exception->getTraceAsString(),
				'previous'		=> serialize( $object->exception->getPrevious() ),
			] );
		}
		else if( $object instanceof Throwable ){
			$data	= array_merge( $data, [
				'type'			=> get_class( $object ),
				'message'		=> $object->getMessage(),
				'code'			=> $object->getCode() ?? '',
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
				'code'			=> $object->code ?? '',
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
		if( property_exists( $object, 'requestId' ) && !empty( $object->requestId ) )
			$data['requestId']	= $object->requestId;
		return $this->model->add( $data, FALSE );
	}

	/**
	 *	@param		Throwable		$exception
	 *	@return		bool|NULL
	 *	@throws		ReflectionException
	 */
	public function log( Throwable $exception ): ?bool
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
				if( !file_exists( $this->logFile ) )
					\CeusMedia\Common\FS\File::new( $this->logFile, TRUE, 0666 );
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
	 *	@param		Throwable		$exception
	 *	@return		FALSE|void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendExceptionAsMail( Throwable $exception )
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
	 *	@throws		ReflectionException
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

	/**
	 *	New strategy: Work strictly in-file to keep file permissions.
	 *	@param		int		$limit
	 *	@return		int
	 */
	protected function importFromLogFileByStrictInlineStrategy( int $limit ): int
	{
		if(
			!file_exists( $this->logFile ) ||
			!is_readable( $this->logFile ) ||
			!is_writable( $this->logFile ) )
			return 0;

		if( FALSE === ( $fp	= fopen( $this->logFile, 'r+' ) ) )
			return 0;

		if( !flock( $fp, LOCK_EX ) ){
			fclose( $fp );
			return 0;
		}

		$importedLines	= 0;
		$remaining		= [];
		while( FALSE === feof( $fp ) ){
			if( FALSE === ( $line = fgets( $fp ) ) )
				break;
			$lineTrimmed	= rtrim( $line, "\r\n" );
			if( $importedLines < $limit ){
				try {
					$this->importLogFileItem( $lineTrimmed );
					$importedLines++;
					continue;
				}
				catch( Throwable $e ){
					$this->env->getLog()?->logException( $e );
					continue;
				}
			}
			$remaining[] = $line;
		}
		ftruncate( $fp, 0 );
		rewind( $fp );
		foreach( $remaining as $line )
			fwrite( $fp, $line );
		fflush( $fp );
		flock( $fp, LOCK_UN );
		fclose( $fp );
		return $importedLines;
	}

	/**
	 *	First implemented strategy: Use tail to create remaining log file after import.
	 *	Has problem: Log file will be recreated under current user, which is NOT the web user if run by job.
	 *	@param		int		$limit
	 *	@return		int
	 */
	protected function importFromLogFileByRecreatingUsingTailStrategy( int $limit = 200 ): int
	{
		if( !file_exists( $this->logFile ) )
			return 0;
		if( 0 === filesize( $this->logFile ) )
			return 0;

		$countDone		= 0;
		$countFail		= 0;
		$handle		= fopen( $this->logFile, 'r' );
		while( !feof( $handle ) && ( $countDone + $countFail ) < $limit ){
			$line	= fgets( $handle );
			if( '' === trim( $line ) )
				continue;
			try{
				$this->importLogFileItem( $line );
				$countDone++;
			}
			catch( Throwable $e ){
				$this->env->getLog()?->logException( $e );
				$countFail++;
			}
		}
		if( 0 === ( $countDone + $countFail ) )
			return 0;

		// @link https://www.baeldung.com/linux/remove-first-line-text-file
		$command	= 'tail -n +%1$d %2$s > %2$s.tmp && mv %2$s.tmp %2$s';
		exec( sprintf( $command, ( $countDone + $countFail + 1 ), $this->logFile ) );
		chmod( $this->logFile, 0666 );
		return $countDone + $countFail;
	}
}
