<?php
class Logic_Sitemap{

	static protected $instance;

	protected $config;
	protected $env;
	protected $links		= array();
    protected $frequencies  = array(
        'always',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'never'
    );

	protected $defaultFrequency	= 'yearly';
	protected $defaultPriority	= 0.1;

	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.resource_sitemap.', TRUE );
	}

	protected function __clone(){}

	public function addLink( $location, $timestamp, $priority = NULL, $frequency = NULL ){
		$priority	= is_null( $priority ) ? $this->defaultPriority : (int) $priority;
		$frequency	= is_null( $frequency ) ? $this->defaultFrequency : trim( strolower( $frequency ) );
        if( $priority < 0 )
            throw new OutOfBoundsException( 'Priority cannot be lower than 0' );
        else if( $priority > 1 )
            throw new OutOfBoundsException( 'Priority cannot be greater than 1' );
        if( !in_array( $frequency, $this->frequencies ) )
            throw new InvalidArgumentException( 'Frequency must with one of '.join( ', ', $this->frequencies ) );
		$this->links[]	= (object) array(
			'location'	=> $location,
			'datetime'	=> date( 'c', $timestamp ),
			'frequency'	=> $frequency,
			'priority'	=> $priority,
		);
	}

	static public function getInstance( CMF_Hydrogen_Environment_Abstract $env ){
		if( !self::$instance )
			self::$instance	= new Logic_Sitemap( $env );
		return self::$instance;
	}

	public function getLinks(){
		return $this->links;
	}
}
?>
