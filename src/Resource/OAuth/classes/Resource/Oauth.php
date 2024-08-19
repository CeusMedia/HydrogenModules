<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Resource_Oauth
{
	protected Environment $env;
	protected Dictionary $config;
	protected Dictionary $moduleConfig;
	protected string $serverUri;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.resource_oauth.', TRUE );
		$this->serverUri	= $this->moduleConfig->get( 'server.URI' );
	}

	protected function getToken(): string
	{
		$token	= $this->env->getSession()->get( 'oauth_access_token' );
		if( $token )
			return $token;
		throw new RuntimeException( 'OAuth access token is missing' );
	}

	protected function handleRequest( $handle )
	{
	}

	public function read( string $resourcePath )
	{
		if( !trim( $resourcePath ) && ltrim( $resourcePath, '/' ) )
			throw new InvalidArgumentException( 'Missing resource path to request' );
		$resourcePath	= ltrim( $resourcePath, '/' );

		$handle	= new NetCurl();
		$handle->setUrl( $this->serverUri.'/'.$resourcePath );
		$handle->setOption( CURLOPT_POST, TRUE );
//		$handle->setOption( CURLOPT_POSTFIELDS, $postData );
		$handle->setOption( CURLOPT_HTTPHEADER, [
			'Authorization: Bearer '.$this->getToken(),
//			'Content-Type: application/x-www-form-urlencoded',
//			'Content-Length: '.strlen( $postData ),
		] );
		$response	= $handle->exec();
		$httpCode	= $handle->getInfo( 'http_code' );
		if( $httpCode < 200 || $httpCode >= 300 )
			throw new RuntimeException( 'Request to resource "'.$resourcePath.'" failed with HTTP code '.$httpCode );
		$response	= json_decode( $response );
		if( in_array( $response->status, ["error", "exception"] ) )
			throw new RuntimeException( 'Request to resource "'.$resourcePath.'" failed: '.$response->data );
		return $response->data;
	}

	/**
	 *	@param		string		$resourcePath
	 *	@param		array		$postData
	 *	@return		mixed
	 */
	public function write( string $resourcePath, array $postData = [] ): mixed
	{
		if( !trim( $resourcePath ) && ltrim( $resourcePath, '/' ) )
			throw new InvalidArgumentException( 'Missing resource path to request' );
		$resourcePath	= ltrim( $resourcePath, '/' );
		$postData		= http_build_query( $postData );
		$handle	= new NetCurl();
		$handle->setUrl( $this->serverUri.'/'.$resourcePath );
		$handle->setOption( CURLOPT_POST, TRUE );
		$handle->setOption( CURLOPT_POSTFIELDS, $postData );
		$handle->setOption( CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$this->getToken(),
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen( $postData ),
		) );
		$response	= $handle->exec();
		$httpCode	= $handle->getInfo( 'http_code' );
		if( $httpCode < 200 || $httpCode >= 300 )
			throw new RuntimeException( 'Request to resource "'.$resourcePath.'" failed with HTTP code '.$httpCode );
		$response	= json_decode( $response );
		if( in_array( $response->status, ["error", "exception"] ) )
			throw new RuntimeException( 'Request to resource "'.$resourcePath.'" failed: '.$response->data );
		return $response->data;
	}
}
