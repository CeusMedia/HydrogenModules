<?php
class Logic_Log_Exception extends CMF_Hydrogen_Logic
{
	protected $logFile;

	protected $model;

	protected $moduleConfig;

	protected $pathLogs;

	public function check( $id, bool $strict = TRUE )
	{
		$exception	= $this->model->get( $id );
		if( $exception )
			return $exception;
		if( $strict )
			throw new RangeException( 'Invalid exception ID' );
		return NULL;
	}

	public function collectData( Exception $exception )
	{
		try{
			@serialize( $exception );
			$content	= (object) array(
				'exception'		=> $exception,
		//		'traceAsHtml'	=> UI_HTML_Exception_Trace::render( $exception ),
				'trace'			=> '',
				'timestamp'		=> time(),
			);
		}
		catch( Exception $_e ){
			$content	= (object) array(
				'message'		=> $exception->getMessage(),
				'code'			=> $exception->getCode(),
				'file'			=> $exception->getFile(),
				'line'			=> $exception->getLine(),
				'trace'			=> $exception->getTraceAsString(),
				'previous'		=> $exception->getPrevious(),
				'timestamp'		=> time(),
			);
		}
		$sessionData	= $this->env->getSession()->getAll();
		if( isset( $sessionData['exception'] ) )
		unset( $sessionData['exception'] );
		if( isset( $sessionData['exceptionRequest'] ) )
		unset( $sessionData['exceptionRequest'] );
		if( isset( $sessionData['exceptionUrl'] ) )
		unset( $sessionData['exceptionUrl'] );
		$content->env				= array(
			'appName'	=> $this->env->getConfig()->get( 'app.name' ),
			'class'		=> get_class( $this->env ),
			'url'		=> $this->env->url,
			'uri'		=> $this->env->uri,
		);
		$content->request			= $this->env->getRequest();
		$content->session			= $sessionData;
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

		$classes	= array_values( array( $content->class ) + $content->classParents );

		$content->resource		= NULL;
		if( in_array( 'Exception_IO', $classes ) )
			$content->resource		= $exception->getResource();
		$content->subject		= NULL;
		if( in_array( 'Exception_Logic', $classes ) )
			$content->subject		= $exception->getSubject();
		return $content;
	}

	public function importFromLogFile( int $limit = 200 ): int
	{
		$count		= 0;
		if( file_exists( $this->logFile ) && filesize( $this->logFile ) === 0 ){
			$handle		= fopen( $this->logFile, 'r' );
			while( !feof( $handle ) && $count < $limit ){
				$line	= fgets( $handle );
				if( strlen( trim( $line ) ) ){
					try{
						$this->importLogFileItem( $line );
					}
					catch( Exception $e ){}
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

	public function importLogFileItem( string $line )
	{
		list($timestamp, $dataEncoded)	= explode( ":", $line );
		$data	= base64_decode( $dataEncoded );
		$object	= @unserialize( $data );
		if( !is_object( $object ) )
			throw new InvalidArgumentException( "Line is not containing an exception data object" );

		$data	= array(
			'status'		=> 0,
			'message'		=> '',
			'trace'			=> '',
			'createdAt'		=> $timestamp,
			'modifiedAt'	=> time(),
		);

		if( isset( $object->exception ) && $object->exception instanceof Exception ){
			$data	= array_merge( $data, array(
				'type'			=> get_class( $object->exception ),
				'message'		=> $object->exception->getMessage(),
				'code'			=> $object->exception->getCode(),
				'file'			=> $object->exception->getFile(),
				'line'			=> $object->exception->getLine(),
				'trace'			=> $object->exception->getTraceAsString(),
				'previous'		=> serialize( $object->exception->getPrevious() ),
			) );
		}

		else if( $object instanceof Exception ){
			$data	= array_merge( $data, array(
				'type'			=> get_class( $object ),
				'message'		=> $object->getMessage(),
				'code'			=> $object->getCode(),
				'file'			=> $object->getFile(),
				'line'			=> $object->getLine(),
				'trace'			=> $object->getTraceAsString(),
				'previous'		=> serialize( $object->getPrevious() ),
			) );
		}
		else{
			$data	= array_merge( $data, array(
				'type'			=> 'Exception',
				'message'		=> $object->message,
				'code'			=> $object->code,
				'file'			=> $object->file,
				'line'			=> $object->line,
				'trace'			=> $object->trace,
			) );
		}
		if( empty( $data['trace'] ) ){
			print_m( $object );die;
		}
		$data['env']	= '';
		if( !empty( $object->env ) )
			$data['env']	= serialize( $object->env );
		if( !empty( $object->request ) )
			$data['request']	= serialize( $object->request );
		if( !empty( $object->session ) )
		$data['session']	= serialize( $object->session );
		return $this->model->add( $data, FALSE );
	}

	public function log( Exception $exception )
	{
		$this->captain->callHook( 'Env', 'logException', $this->env, ['exception' => $exception] );
	}

	public function saveCollectedDataToLogFile( $data )
	{
		if( $this->moduleConfig->get( 'file.active' ) ){
			if( trim( $this->moduleConfig->get( 'file.name' ) ) ){
				$msg	= time().":".base64_encode( serialize( $data ) );
				error_log( $msg.PHP_EOL, 3, $this->logFile );
			}
		}
	}

	public function sendCollectedDataAsMail( $data )
	{
		if( !$this->moduleConfig->get( 'mail.active' ) )
			return FALSE;
		die( 'Not implemented, yet.' );
	}

	public function sendExceptionAsMail( Exception $exception )
	{
		if( !$this->moduleConfig->get( 'mail.active' ) )
			return FALSE;
		$hasReceivers	= trim( $this->moduleConfig->get( 'mail.receivers' ) );
		$hasMailModule	= $this->env->getModules()->has( 'Resource_Mail', TRUE );
		$hasBaseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		if( !( $hasReceivers && $hasMailModule && $hasBaseUrl ) )
			return FALSE;
		$language		= $this->env->getLanguage()->getLanguage();
		$logicMail		= Logic_Mail::getInstance( $this->env );
		$mail			= new Mail_Log_Exception( $this->env, array( 'exception' => $exception ) );
		$receivers		= preg_split( '/(,|;)/', $this->moduleConfig->get( 'mail.receivers' ) );
		foreach( $receivers as $receiver ){
			if( trim( $receiver ) ){
				$receiver	= (object) array( 'email' => $receiver );
				$logicMail->handleMail( $mail, $receiver, $language );
			}
		}
	}

	protected function __onInit()
	{
		$this->model		= new Model_Log_Exception( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$this->pathLogs		= $this->env->getConfig()->get( 'path.logs' );
		if( $this->env->getModules()->has( 'Frontend' ) ){
			$frontend			= Logic_Frontend::getInstance( $this->env );
			$this->pathLogs		= $frontend->getPath( 'logs' );
			$moduleConfig		= $frontend->getModuleConfigValues( 'Server_Log_Exception' );;
			$this->moduleConfig	= new ADT_List_Dictionary( $moduleConfig );
		}
		$this->logFile		= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
	}
}
