<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Reader as HttpReader;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Resource_Provision_Client
{
	protected Environment $env;
	protected Dictionary $moduleConfig;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.resource_provision.', TRUE );
	}

	public function getLicense( $licenseId )
	{
		return $this->request( 'provision/rest/getLicense/'.$licenseId );
	}

	public function getProductLicenses( $productId )
	{
		return $this->request( 'provision/rest/getLicenses/'.$productId );
	}

	public function getUserLicenseKey( $userId )
	{
		$modelUser	= new Model_User( $this->env );
		$user		= $modelUser->get( $userId );
		if( !$user )
			throw new InvalidArgumentException( 'Invalid user ID' );
		$url		= 'provision/rest/hasActiveKey';
		$postData	= [
			'productId'	=> $this->moduleConfig->get( 'productId' ),
			'userId'	=> $user->accountId,
		];
		return $this->request( $url, $postData );
	}

	public function request( $url, $postData = NULL )
	{
		$productId		= $this->moduleConfig->get( 'productId' );
		$server			= $this->moduleConfig->getAll( 'server.', TRUE );
		if( !preg_match( "@^[a-z]+://@", $url ) )
			$url		= $server->get( 'url' ).$url;
		$username		= $server->get( 'username' );
		$password		= $server->get( 'password' );
		$serverRequest	= new HttpReader();
		if( $username && $password )
			$serverRequest->setBasicAuth( $username, $password );
		if( is_array( $postData ) )
			$response	= $serverRequest->post( $url, $postData );
		else
			$response	= $serverRequest->get( $url );

		$status	= HttpStatus::getCode( $response->getStatus() );
		if( $status === 302 && $response->headers->hasField( 'Location' ) ){
			$redirect = $response->headers->getField( 'Location' )[0]->getValue();
			if( parse_url( $url, PHP_URL_HOST ) !== parse_url( $redirect, PHP_URL_HOST ) )
				throw new DomainException( 'Relocation to another domain is not allowed at the moment' );
			return $this->request( $redirect, $postData );
		}
		if( $status !== 200 )
			throw new RuntimeException( 'Request on provision server failed (HTTP response code '.$status.')' );
		$response	= json_decode( $response->getBody() );
		if( !$response )
			throw new RuntimeException( 'Request on provision server ('.$url.') failed (no JSON data returned)' );
		if( $response->status === "error" ){
			$message	= 'Request on provision server failed (Error: %s)';
			throw new RuntimeException( sprintf( $message, $response->data->message ) );
		}
		if( $response->status === "exception" ){
			$message	= 'Request on provision server failed (Exception: %s)';
			throw new RuntimeException( sprintf( $message, $response->data->message ) );
		}
		return $response->data;
	}
}
