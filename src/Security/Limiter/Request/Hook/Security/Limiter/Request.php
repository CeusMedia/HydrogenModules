<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Env as CommonEnv;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Security_Limiter_Request extends Hook
{
	public function checkLimits(): void
	{
		if( CommonEnv::isCli() ) return;													//  bypass CLI requests
		if( !$this->env->getModules()->has( 'Server_Log_Request' ) ) return;		//  no request logging module installed

		/** @var Dictionary $config */
		$config		= $this->env->getConfig()->getAll( 'module.security_limiter_request.', TRUE );
		$request	= $this->env->getRequest();

		if( $config->get( 'whitelist.ajax' ) && $request->isAjax() )
			return;

		$remoteIp	= $_SERVER['REMOTE_ADDR'];

		if( $this->passByWhitelistSelf( $config, $remoteIp ) ) return;						//  bypass request from own IP
		if( $this->passByWhitelistIps( $config, $remoteIp ) ) return;						//  bypass request from whitelisted IP

		$this->applyLimits( $config, $remoteIp, $request );
	}

	protected function applyLimits( Dictionary $config, string $remoteIp, HttpRequest $request ): void
	{
		$model			= new Model_Log_Request( $this->env );
		$method			= strtoupper( $request->getMethod()->get() );
		$configPrefix	= match( $method ){
			'GET'	=> 'limit.get.',
			default	=> 'limit.other.',
		};

		$generalIndices		= [
			'ip'		=> $remoteIp,
			'method'	=> $method,
		];

		$limitPerSecond	= $config->get( $configPrefix.'second' );
		if( 0 !== $limitPerSecond ){
			$indices	= array_merge( $generalIndices, ['timestamp' => time() - 1] );
			if( $model->countByIndices( $indices ) >= $limitPerSecond )
				$this->respondError();
		}

		$limitPerMinute	= $config->get( $configPrefix.'minute' );
		if( 0 !== $limitPerMinute ){
			$indices	= array_merge( $generalIndices, ['timestamp' => time() - 60] );
			if( $model->countByIndices( $indices ) >= $limitPerMinute )
				$this->respondError();
		}

		$limitPerHour	= $config->get( $configPrefix.'hour' );
		if( 0 !== $limitPerHour ){
			$indices	= array_merge( $generalIndices, ['timestamp' => time() - 24 * 60] );
			if( $model->countByIndices( $indices ) >= $limitPerHour )
				$this->respondError();
		}
	}

	protected function passByWhitelistIps( Dictionary $config, string $remoteIp ): bool
	{
		/** @var string $configuredIps */
		$configuredIps	= $config->get( 'whitelist.ips', '' );
		if( '' === trim( $configuredIps ) )
			return FALSE;

		$whitelistedIps	= preg_split( '/\s*,\s*/', trim( $configuredIps ) );
		return in_array( $remoteIp, $whitelistedIps );
	}

	protected function passByWhitelistSelf( Dictionary $config, string $remoteIp ): bool
	{
		if( !$config->get( 'whitelist.self' ) )
			return FALSE;

		$baseUrl	= $this->env->getBaseUrl();
		if( '' === $baseUrl )
			return FALSE;

		$ownHost	= parse_url( $baseUrl, PHP_URL_HOST );
		if( FALSE === $ownHost )
			return FALSE;

		$ownIp		= gethostbyname( $ownHost );
		if( $ownIp === $ownHost || $ownIp !== $remoteIp  )
			return FALSE;

		return TRUE;
	}

	protected function respondError(): void
	{
		$body	= '429 Too Many Requests';
		/** @var HttpResponse $response */
		$response	= $this->env->getResponse();
		$response->setStatus( 429 );
		$response->setHeader( 'Content-type', 'text/html; charset=utf-8' );
		$response->setBody( 'Request limit reached' );
		$response->send();
	}
}