<?php

use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class Resource_DevCenter
{
	/**	@var	Environment		$env		*/
	protected $env;

	protected static $instance	= NULL;

	protected $modules			= [];

	protected $resources		= [];

	protected function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->modules	= array(
			'request'		=> [
				'label'		=> "Request",
				'resource'	=> $_REQUEST,
			],
			'session'		=> [
				'label'		=> "Session",
				'resource'	=> $_SESSION,
			],
			'cookie'		=> [
				'label'		=> "Cookie",
				'resource'	=> $_COOKIE,
			],
			'files'		=> [
				'label'		=> "Files",
				'resource'	=> $_FILES,
			],
			'env'			=> [
				'label'		=> "Environment",
				'resource'	=> $_ENV,
			],
			'server'		=> [
				'label'		=> "Server",
				'resource'	=> $_SERVER,
			],
		);
	}

	protected function __clone()
	{
	}

	public function add( string $key, string $label, $value ): self
	{
		$this->resources[]	= (object) [
			'key'	=> $key,
			'label'	=> $label,
			'value'	=> $value,
		];
		return $this;
	}

	public function addByModule( string $module, $label = NULL ): self
	{
		if( !array_key_exists( $module, $this->modules ) )
			throw new DomainException( 'Unknown module "'.$module.'"' );
		$label		= strlen( trim( $label ) ) ? $label : $this->modules[$module]['label'];
		$this->add( $module, $label, $this->modules[$module]['resource'] );
		return $this;
	}

	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new Resource_DevCenter( $env );
		return self::$instance;
	}

	public function getResources(): array
	{
		return $this->resources;
	}
}
