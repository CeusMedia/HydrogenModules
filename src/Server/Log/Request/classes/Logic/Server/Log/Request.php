<?php

use CeusMedia\Common\Net\HTTP\Header\Field as HeaderField;
use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

class Logic_Server_Log_Request extends SharedLogic
{
	protected Model_Log_Request $model;
	protected int|string|NULL $currentRequestId	= NULL;

	public function countRequestsOfCurrentIpToCurrentRequest( ?string $method = NULL, DateInterval|DateTime|int|string $since = NULL, DateInterval|DateTime|int|string $until = NULL ): int
	{
		$path	= $this->env->getRequest()->get( 'path' );
		$ip		= $_SERVER['REMOTE_ADDR'];
		return $this->countRequestsOfIp( $ip, $method, $path, $since, $until );

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
		if( NULL !== $since )
			$since	= $this->convertSomeDateInputsToDateTime( $since );
		if( NULL !== $until )
			$until	= $this->convertSomeDateInputsToDateTime( $until );

		if( NULL === $since && NULL === $until )
			return $this->model->countFast( $indices );
		if( NULL !== $since && NULL !== $until )
			$indices['timestamp']	= '>< '.$since->format( 'Y-m-d H:i:s' ).' '.$until->format( 'Y-m-d H:i:s' );
		else if( NULL !== $since )
			$indices['timestamp']	= '>= '.$since->format( 'Y-m-d H:i:s' );
		else if( NULL !== $until )
			$indices['timestamp']	= '<= '.$since->format( 'Y-m-d H:i:s' );
		return $this->model->count( $indices );
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
		$this->currentRequestId	= $this->model->add( $data );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todo		to be implemented: add new table for responses and link both tables, store response code, size and runtime
	 */
	public function logCurrentResponse(): void
	{
		if( NULL === $this->currentRequestId )
			return;
		$this->model->edit( $this->currentRequestId, [
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model	= new Model_Log_Request( $this->env );
	}

	/**
	 *	@return		Entity_Log_Request
	 */
	protected function collectData(): Entity_Log_Request
	{
		$ip			= NULL;
		$sessionId	= NULL;
		$method		= 'CLI';
		$url		= NULL;
		$cookie		= NULL;
		$session	= NULL;
		$headers	= NULL;

		if( !CeusMedia\Common\Env::isCli() ){
			$ip			= getenv( 'REMOTE_ADDR' );
			$sessionId	= $this->env->getSession()->getSessionID();
			$method		= getenv( 'REQUEST_METHOD' );
			$url		= substr( getenv( 'REQUEST_URI' ), 0, 255 );
			if( $this->env->has( 'cookie' ) )
				$cookie		= $this->env->get( 'cookie' )->getAll();
			$session	= $this->env->get( 'session' )->getAll();
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
			'session'	=> json_encode( $session ),
			'cookie'	=> json_encode( $cookie ),
			'headers'	=> json_encode( $headers ),
			'referer'	=> $_SERVER['HTTP_REFERER'] ?? '',
			'userAgent'	=> $_SERVER['HTTP_USER_AGENT'] ?? '',
			'timestamp'	=> $date->format( 'Y-m-d H:i:s.u' ),
		] );
	}

	protected function convertSomeDateInputsToDateTime( DateInterval|DateTime|int|string $input): DateTime
	{
		if( is_int( $input ) ){
			if( $input < 100 * 24 * 60 * 60 )
				return $this->convertSomeDateInputsToDateTime( $input.' seconds' );
			$date	= new DateTime();
			$date->setTimestamp( $input );
			return $date;
		}
		if( is_string( $input ) ){
			if( str_starts_with( $input, 'P' ) )
				return $this->convertSomeDateInputsToDateTime( new DateInterval( $input ) );
			$date	= new DateTime( 'now' );
			return $date->modify( '-'.$input );
		}
		if( $input instanceof DateInterval ){
			$date	= new DateTime( 'now' );
			return $date->sub( $input );
		}
		return $input;
	}
}