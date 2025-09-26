<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\Net\HTTP\Header\Field as HeaderField;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_Server_Log_Request extends SharedLogic
{
	protected Model_Log_Request $model;
	protected ModuleDefinition $module;
	protected bool $isActive					= FALSE;
	protected int|string|NULL $currentRequestId	= NULL;

	public function countRequestsOfCurrentIpToCurrentRequest( ?string $method = NULL, DateInterval|DateTime|int|string $since = NULL, DateInterval|DateTime|int|string $until = NULL ): int
	{
		$path	= $this->env->getRequest()->get( 'path' );
		$ip		= $_SERVER['REMOTE_ADDR'];
		return $this->countRequestsOfIp( $ip, $method, $path, $since, $until );
	}

	public function countRequestsInInterval( DateInterval|DateTime|int|string $since = NULL, DateInterval|DateTime|int|string $until = NULL ): int
	{
		if( NULL === $since && NULL === $until )
			return 0;

		if( NULL !== $since && NULL !== $until )
			$timestamp	= vsprintf( '>< %s %s', [
				$this->convertSomeDateInputsToDateTime( $since )->format( 'Y-m-d H:i:s' ),
				$this->convertSomeDateInputsToDateTime( $until )->format( 'Y-m-d H:i:s' ),
			] );
		else if( NULL !== $since )
			$timestamp	= '>= '.$this->convertSomeDateInputsToDateTime( $since )
				->format( 'Y-m-d H:i:s' );
		else if( NULL !== $until )
			$timestamp	= '<= '.$this->convertSomeDateInputsToDateTime( $until )
				->format( 'Y-m-d H:i:s' );
		return $this->model->count( ['timestamp' => $timestamp] );
	}

	public function countRequestsOfIp( string $ip, ?string $method = NULL, ?string $path = NULL, DateInterval|DateTime|int|string $since = NULL, DateInterval|DateTime|int|string $until = NULL ): int
	{
		$indices	= ['ip'	=> $ip];
		if( NULL !== $method )
			$indices['method']	= strtoupper( $method );
		if( NULL !== $path ){
			if( !str_starts_with( $path, '/' ) )
				$path	= '/'.$path;
			$indices['url']	= $path.'%';
		}
		if( NULL === $since && NULL === $until )
			return $this->model->countFast( $indices );

		if( NULL !== $since && NULL !== $until )
			$indices['timestamp']	= vsprintf( '>< %s %s', [
				$this->convertSomeDateInputsToDateTime( $since )->format( 'Y-m-d H:i:s' ),
				$this->convertSomeDateInputsToDateTime( $until )->format( 'Y-m-d H:i:s' ),
			] );
		else if( NULL !== $since )
			$indices['timestamp']	= '>= '.$this->convertSomeDateInputsToDateTime( $since )
				->format( 'Y-m-d H:i:s' );
		else if( NULL !== $until )
			$indices['timestamp']	= '<= '.$this->convertSomeDateInputsToDateTime( $until )
				->format( 'Y-m-d H:i:s' );
		return $this->model->count( $indices );
	}

	/**
	 *	Tries to import file log entries to database.
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function importFromFileToDatabase(): int
	{
		$fileName	= $this->module->config['file']->value;
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		$counter	= 0;
		$requests	= [];
		if( file_exists( $filePath ) ){
			foreach( FileReader::loadArray( $filePath ) as $line ){
				if( '' === trim( $line ) )
					continue;
				list( $id, $data )	= explode( ' ', $line, 2 );
				if( str_starts_with( $id, '@' ) ){							//  response on a request
					$id	= substr( $id, 1 );
					if( !array_key_exists( $id, $requests ) )
						continue;
					$this->model->edit( $requests[$id], json_decode( $data ), FALSE );
				}
				else{																//  request itself
					$requestId	= $this->model->add( Entity_Log_Request::fromArray( json_decode( $data, TRUE ) ) );
					$requests[$id]	= $requestId;
					$counter++;
				}
			}
			@unlink( $filePath );
		}
		return $counter;
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function logCurrentRequest(): void
	{
		if( NULL !== $this->currentRequestId )
			return;
		$data	= $this->collectData();
		switch( $this->module->config['saveTo']->value ){
			case 'file':
				$this->currentRequestId	= $this->logRequestEntityToFile( $data );
				break;
			case 'database':
			default:
				$this->currentRequestId	= $this->model->add( $data );
				break;
		}
	}

	/**
	 *	@param		int			$status
	 *	@param		string		$mimeType
	 *	@param		string		$content
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function logCurrentResponse( int $status, string $mimeType, string $content ): void
	{
		if( NULL === $this->currentRequestId )
			return;

		$data	= [
			'responseCode'		=> $status,
			'responseTime'		=> $this->env->getRuntime()->get(),
			'responseType'		=> $mimeType,
			'responseContent'	=> $content,
		];
		switch( $this->module->config['saveTo']->value ){
			case 'file':
				$this->currentRequestId	= $this->logResponseToFile( $data );
				break;
			case 'database':
			default:
				$this->model->edit( $this->currentRequestId, $data, FALSE );
				break;
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model	= new Model_Log_Request( $this->env );
		$this->module	= $this->env->getModules()->get( 'Server_Log_Request' );
		$this->isActive	= $this->module->config['active']->value;
	}

	/**
	 *	@return		Entity_Log_Request
	 */
	protected function collectData(): Entity_Log_Request
	{
		$ip			= '';
		$sessionId	= '';
		$method		= 'CLI';
		$url		= NULL;
		$cookieData		= NULL;
		$sessionData	= NULL;
		$headers	= NULL;

		if( !CeusMedia\Common\Env::isCli() ){
			$ip			= getenv( 'REMOTE_ADDR' );
			/** @var HttpPartitionSession $session */
			$session		= $this->env->getSession();
			$sessionId		= $session->getSessionID();
			$sessionData	= $session->getAll();
			$method		= getenv( 'REQUEST_METHOD' );
			$url		= substr( getenv( 'REQUEST_URI' ) ?: '', 0, 255 );
			if( $this->env->has( 'cookie' ) ){
				/** @var HttpCookie $cookie */
				$cookie		= $this->env->get( 'cookie' );
				$cookieData	= $cookie->getAll();
			}
			$headers	= array_map( static function( HeaderField $field ){
				return $field->toString();
			}, $this->env->getRequest()->getHeaders()->getFields() );
		}

		$date	= DateTime::createFromFormat( 'U.u', (string) microtime( TRUE ) );

		return Entity_Log_Request::fromArray( [
			'ip'		=> $ip,
			'sessionId'	=> $sessionId,
			'method'	=> $method,
			'url'		=> $url,
			'request'	=> json_encode( $this->env->getRequest()->getAll() ),
			'session'	=> json_encode( $sessionData ),
			'cookie'	=> json_encode( $cookieData ),
			'headers'	=> json_encode( $headers ),
			'referer'	=> $_SERVER['HTTP_REFERER'] ?? '',
			'userAgent'	=> $_SERVER['HTTP_USER_AGENT'] ?? '',
			'timestamp'	=> $date->format( 'Y-m-d H:i:s.u' ),
		] );
	}

	protected function convertSomeDateInputsToDateTime( DateInterval|DateTime|int|string $input ): DateTime
	{
		if( $input instanceof DateInterval ){
			$date	= new DateTime( 'now' );
			return $date->sub( $input );
		}
		else if( is_string( $input ) ){
			if( str_starts_with( $input, 'P' ) )
				return $this->convertSomeDateInputsToDateTime( new DateInterval( $input ) );
			$date	= new DateTime( 'now' );
			return $date->modify( '-'.$input );
		}
		else if( is_int( $input ) ){
			if( $input < 100 * 24 * 60 * 60 )
				return $this->convertSomeDateInputsToDateTime( $input.' seconds' );
			$date	= new DateTime();
			$date->setTimestamp( $input );
			return $date;
		}
		/** @var DateTime $input */
		return $input;
	}

	/**
	 *	Stores collected request entity to log file.
	 *	This is for lazy mode, which is not recommended ATM, but faster on production.
	 *	@param		Entity_Log_Request		$request
	 *	@return		string
	 */
	protected function logRequestEntityToFile( Entity_Log_Request $request ): string
	{
		$fileName	= $this->module->config['file']->value;
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		$id			= \CeusMedia\Common\Alg\ID::uuid();
		error_log( $id.' '.json_encode( $request->toArray() ).PHP_EOL, 3, $filePath );
		return $id;
	}

	/**
	 *	@param		array		$data
	 *	@return		void
	 */
	protected function logResponseToFile( array $data )
	{
		$fileName	= $this->module->config['file']->value;
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		error_log( '@'.$this->currentRequestId.' '.json_encode( $data ).PHP_EOL, 3, $filePath );
	}
}
