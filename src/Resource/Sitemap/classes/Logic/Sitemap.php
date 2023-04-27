<?php

use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\HydrogenFramework\Environment;

class Logic_Sitemap{

	static protected $instance;

	protected $config;
	protected $env;
	protected $links		= [];
	protected $frequencies  = [
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never'
	];

	protected $defaultFrequency	= 'yearly';
	protected $defaultPriority	= 0.1;

    public $providers	= [
//		'ask'		=> "http://submissions.ask.com/ping?sitemap=%s",
		'bing'		=> "http://www.bing.com/ping?sitemap=%s",
		'google'	=> "http://www.google.com/webmasters/tools/ping?sitemap=%s",
		'moreover'	=> "http://api.moreover.com/ping?u=%s",
    ];

	protected function __construct( Environment $env ){
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.resource_sitemap.', TRUE );
	}

	protected function __clone(){}

	public function addLink( $location, $timestamp, $priority = NULL, $frequency = NULL ){
		$priority	= is_null( $priority ) ? $this->defaultPriority : (float) $priority;
		$frequency	= is_null( $frequency ) ? $this->defaultFrequency : trim( strtolower( $frequency ) );
		if( $priority < 0 )
			throw new OutOfBoundsException( 'Priority cannot be lower than 0' );
		else if( $priority > 1 )
			throw new OutOfBoundsException( 'Priority cannot be greater than 1' );
		if( !in_array( $frequency, $this->frequencies ) )
			throw new InvalidArgumentException( 'Frequency must with one of '.join( ', ', $this->frequencies ) );
		foreach( $this->links as $link )
			if( $location === $link->location )
				throw new InvalidArgumentException( 'Link already set by location: '.$location );
		$this->links[]	= (object) array(
			'location'	=> $location,
			'datetime'	=> $timestamp > 0 ? date( 'c', (int) $timestamp ) : NULL,
			'frequency'	=> $frequency,
			'priority'	=> $priority,
		);
	}

	static public function getInstance( Environment $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Sitemap( $env );
		return self::$instance;
	}

	public function getLinks(){
		return $this->links;
	}

	public function submitToProviders(){
		$result	= [];
		foreach( $this->providers as $key => $value ){
			$url	= sprintf( $value, urlencode( $this->env->url.'sitemap' ) );
//print_m( $this->env->getConfig()->getAll() );
//xmp( $url );die;
			try{
				$curl   = new NetCurl( $url );
				$curl->exec();
				if( (int) $curl->getInfo( NetCurl::INFO_HTTP_CODE ) === 200 ){
					$result[$key]	= "OK";
				}
			}
			catch( Exception $e ){
				$result[$key]	= "FAIL: ".$e->getMessage();
			}
		}
		return $result;
	}
}
