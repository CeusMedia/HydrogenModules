<?php
class Resource_DevCenter{

	/**	@var	CMF_Hydrogen_Environment_Abstract		$env		*/
	protected $env;

	protected static $instance	= NULL;

	protected $modules			= array();

	protected $resources		= array();

	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env		= $env;
		$this->modules	= array(
			'request'		=> array(
				'label'		=> "Request",
				'resource'	=> $_REQUEST,
			),
			'session'		=> array(
				'label'		=> "Session",
				'resource'	=> $_SESSION,
			),
			'cookie'		=> array(
				'label'		=> "Cookie",
				'resource'	=> $_COOKIE,
			),
			'files'		=> array(
				'label'		=> "Files",
				'resource'	=> $_FILES,
			),
			'env'			=> array(
				'label'		=> "Environment",
				'resource'	=> $_ENV,
			),
			'server'		=> array(
				'label'		=> "Server",
				'resource'	=> $_SERVER,
			),
		);
	}

	protected function __clone(){}

	public function add( $key, $label, $value ){
		$this->resources[]	= (object) array(
			'key'	=> $key,
			'label'	=> $label,
			'value'	=> $value,
		);
	}

	public function addByModule( $module, $label = NULL ){
		if( !array_key_exists( $module, $this->modules ) )
			throw new DomainException( 'Unknown module "'.$module.'"' );
		$label		= strlen( trim( $label ) ) ? $label : $this->modules[$module]['label'];
		$this->add( $module, $label, $this->modules[$module]['resource'] );
	}

	public static function getInstance( CMF_Hydrogen_Environment_Abstract $env ){
		if( !self::$instance )
			self::$instance	= new Resource_DevCenter( $env );
		return self::$instance;
	}

	public function getResources(){
		return $this->resources;
	}
}
?>
