<?php

use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\HydrogenFramework\Environment;

class Resource_Uberlog
{
	protected Environment $env;
	protected string $url;
	protected string $category;
	protected string $host;
	protected string $client;
	protected string $userAgent;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->url			= $env->getConfig()->get( 'module.resource_uberlog.uri' );
		$this->category		= "test";
		$this->host			= getEnv( 'HTTP_HOST' );
		$this->client		= $env->getConfig()->get( 'app.name' );
		$this->userAgent	= getEnv( 'HTTP_USER_AGENT' );
	}

	public function report( $data ): string
	{
		if( $data instanceof Exception ){
			$data	= [
				'message'	=> $data->getMessage(),
				'code'		=> $data->getCode(),
				'source'	=> $data->getFile(),
				'line'		=> $data->getLine(),
				'type'		=> get_class( $data ),
			];
		}

		if( !array_key_exists( 'category', $data ) && $this->category )
			$data['category']	= $this->category;
		if( !array_key_exists( 'client', $data ) && $this->client )
			$data['client']	= $this->client;
		if( !array_key_exists( 'host', $data ) && $this->host )
			$data['host']	= $this->host;
		if( !array_key_exists( 'userAgent', $data ) && $this->userAgent )
			$data['userAgent']	= $this->userAgent;
		$curl	= new NetCurl( $this->url.'/record' );
		$curl->setOption( CURLOPT_RETURNTRANSFER, TRUE );
		$curl->setOption( CURLOPT_POST, TRUE );
		$curl->setOption( CURLOPT_POSTFIELDS, $data );
		return $curl->exec();
	}
}
