<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Env as CommonEnv;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Response as HttpResponse;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Security_Limiter_Request extends Hook
{
	const INTERVAL_SECOND	= 1;
	const INTERVAL_MINUTE	= 2;
	const INTERVAL_HOUR		= 3;

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
		$logic		= new Logic_Server_Log_Request( $this->env );
		$method		= strtoupper( $request->getMethod()->get() );

		$configPrefix	= match( $method ){
			'GET'	=> 'limit.get.',
			default	=> 'limit.other.',
		};

		$limitPerSecond	= $config->get( $configPrefix.'second' );
		if( 0 !== $limitPerSecond ){
			$since		= new DateInterval( 'PT1S' );
			$nrRequests	= $logic->countRequestsOfIp( $remoteIp, $method, NULL, $since );
			if( $nrRequests >= $limitPerSecond )
				$this->respondError( $method, self::INTERVAL_SECOND, $limitPerSecond, $nrRequests );
		}

		$limitPerMinute	= $config->get( $configPrefix.'minute' );
		if( 0 !== $limitPerMinute ){
			$since		= new DateInterval( 'PT1M' );
			$nrRequests	= $logic->countRequestsOfIp( $remoteIp, $method, NULL, $since );
			if( $nrRequests >= $limitPerMinute )
				$this->respondError( $method, self::INTERVAL_MINUTE, $limitPerMinute, $nrRequests );
		}

		$limitPerHour	= $config->get( $configPrefix.'hour' );
		if( 0 !== $limitPerHour ){
			$since		= new DateInterval( 'PT1H' );
			$nrRequests	= $logic->countRequestsOfIp( $remoteIp, $method, NULL, $since );
			if( $nrRequests >= $limitPerHour )
				$this->respondError( $method, self::INTERVAL_HOUR, $limitPerHour, $nrRequests );
		}
	}

	protected function passByWhitelistIps( Dictionary $config, string $remoteIp ): bool
	{
		/** @var string $configuredIps */
		$configuredIps	= $config->get( 'whitelist.ips', '' );
		if( '' === trim( $configuredIps ) )
			return FALSE;

		$whitelistedIps	= preg_split( '/\s*,\s*/', trim( $configuredIps ) );
		return in_array( $remoteIp, $whitelistedIps, TRUE );
	}

	protected function passByWhitelistSelf( Dictionary $config, string $remoteIp ): bool
	{
		if( !$config->get( 'whitelist.self' ) )
			return FALSE;

		if( '::1' === $remoteIp )
			return TRUE;

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

	protected function respondError( string $method, int $interval, int $limit, int $number ): void
	{
		$language	= $this->env->getLanguage();
		$title		= "Error 429 - Too Many Requests";

		$mainWords	= $language->getSection( 'main', 'main' );
		$hookWords	= $language->getWords( 'security/limiter/request' )['hook'] ?? [];

		if( '' !== trim( $hookWords['title'] ?? '' ) )
			$title	= $hookWords['title'];

		if( '' !== trim( $mainWords['title'] ?? '' ) )
			$title	.= ' | '.$mainWords['title'];

		$timeNeeded	= $this->env->getRuntime()->get( 3, 0 );
		$body	= '
<html lang="'.$this->env->getLanguage()->getLanguage().'">
	<head>
		<meta charset="UTF-8">
		<title>'.$title.'</title>
	</head>
	<body data-time-needed="'.$timeNeeded.'ms">
		<h1>'.( $hookWords['heading'] ?? '429 Too Many Requests' ).'</h1>
		<div style="margin-bottom: 1.5rem">'.( $hookWords['message'] ?? '' ).'</div>
	</body>
</html>';

		/** @var HttpResponse $response */
		$response	= $this->env->getResponse();
		$response->setStatus( 429 );
		$response->setHeader( 'Content-type', 'text/html; charset=utf-8' );
		$response->setBody( $body );
		$response->send();
	}
}
