<?php
class Resource_Mangopay{

	protected $api;
 	static protected $instance;

	protected function __construct( $env ){
		$this->api	= new \MangoPay\MangoPayApi();
		$this->env	= $env;
		$config		= $this->env->getConfig()->getAll( 'module.resource_payment_mangopay.', TRUE );
		$config->set( 'log.target', 'mangopay.log' );
		$mode		= $config->get( 'api.mode' );
		if( $config->has( 'api.url.'.$mode ) )
			$this->api->Config->BaseUrl		= $config->get( 'api.url.'.$mode );
		$this->api->Config->ClientId		= $config->get( 'client.id' );
		$this->api->Config->ClientPassword	= $config->get( 'client.password' );
		$this->api->Config->TemporaryFolder	= sys_get_temp_dir();
		$this->defaultPagination	= new \MangoPay\Pagination();
		$this->defaultSorting		= new \MangoPay\Sorting();
		$this->defaultSorting->AddField( 'CreationDate', 'DESC' );

		$logger		= new \Monolog\Logger('sample-logger');
		$logger->pushHandler( new \Monolog\Handler\StreamHandler(
			$config->get( 'log.target' ),
			\Monolog\Logger::ERROR | \Monolog\Logger::WARNING | \Monolog\Logger::NOTICE | \Monolog\Logger::CRITICAL | \Monolog\Logger::ALERT
		) );
		$this->api->setLogger($logger);
	}

	public function __get( $name ){
		return $this->api->$name;
	}

	static public function getInstance( $env ){
		if( !self::$instance ){
			self::$instance	= new Resource_Mangopay( $env );
		}
		return self::$instance;
	}

	public function getDefaultPagination(){
		return clone( $this->defaultPagination );
	}

	public function getDefaultSorting(){
		return clone( $this->defaultSorting );
	}
}
