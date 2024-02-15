<?php

use CeusMedia\HydrogenFramework\Environment;
use Stripe\Stripe;

class Resource_Stripe
{
	protected Environment $env;
	protected static self $instance;
//	protected $api;

	protected function __construct( Environment $env )
	{
		$this->env	= $env;
		$config		= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
		Stripe::setApiKey( $config->get( 'api.key.secret' ) );


//		$this->defaultPagination	= new \Stripe\Pagination();
//		$this->defaultSorting		= new \Stripe\Sorting();
//		$this->defaultSorting->AddField( 'CreationDate', 'DESC' );

/*		$logger		= new \Monolog\Logger('sample-logger');
		$logger->pushHandler( new \Monolog\Handler\StreamHandler(
			$config->get( 'log.target' ),
			\Monolog\Logger::ERROR | \Monolog\Logger::WARNING | \Monolog\Logger::NOTICE | \Monolog\Logger::CRITICAL | \Monolog\Logger::ALERT
		) );
		$this->api->setLogger($logger);*/
	}

	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance ){
			self::$instance	= new Resource_Stripe( $env );
		}
		return self::$instance;
	}
/*
	public function getDefaultPagination(){
		return clone( $this->defaultPagination );
	}

	public function getDefaultSorting(){
		return clone( $this->defaultSorting );
	}*/
}
