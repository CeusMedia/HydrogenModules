<?php
class Resource_Stripe{

	protected $api;
 	static protected $instance;

	protected function __construct( $env ){
		$this->env	= $env;
		$config		= $this->env->getConfig()->getAll( 'module.resource_payment_stripe.', TRUE );
		\Stripe\Stripe::setApiKey( $config->get( 'api.key.secret' ) );


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

	static public function getInstance( $env ){
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
