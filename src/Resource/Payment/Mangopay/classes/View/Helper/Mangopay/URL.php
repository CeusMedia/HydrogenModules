<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_URL extends View_Helper_Mangopay_Abstract
{
	protected $url;
	protected $backwardTo			= TRUE;
	protected $forwardTo			= TRUE;
	protected $from					= TRUE;
	protected $parameters			= [];

	public static function renderStatic( Environment $env, $url, $forwardTo = TRUE, $backwardTo = TRUE, $from = TRUE )
	{
		$instance	= new self( $env );
		$instance->set( $url );
		$instance->setForwardTo( $forwardTo );
		$instance->setBackwardTo( $backwardTo );
		$instance->setFrom( $from );
		return $instance->render();
	}

	public function render()
	{
		$param		= [];
		foreach( $this->parameters as $key => $value )
			if( !in_array( $value, [FALSE, NULL] ) )
				if( strlen( trim( $value ) ) )
					$param[$key]	= (string) $value;

		$param		= $param ? '?'.http_build_query( $param, NULL, '&' ) : '';
		return $this->url.$param;
	}

	public function set( $url )
	{
		$this->url	= $url;
		return $this;
	}

	public function setBackwardTo( $path = NULL )
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'backwardTo' );
		$this->setParameter( 'backwardTo', (string) $path, TRUE );
		return $this;
	}

	public function setForwardTo( $path = NULL )
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'forwardTo' );
		$this->setParameter( 'forwardTo', (string) $path, TRUE );
		return $this;
	}

	public function setFrom( $path = NULL )
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'from' );
		$this->setParameter( 'from', (string) $path, TRUE );
		return $this;
	}

	public function setParameter( $key, $value, $override = FALSE, $strict = TRUE )
	{
		if( !strlen( trim( $key ) ) )
			throw new \DomainException( 'Parameter key cannot be empty' );
		if( array_key_exists( $key, $this->parameters ) ){
			if( !$override ){
				if( $strict )
					throw new \RangeException( 'Parameter with key "'.$key.'" is already set' );
				return $this;
			}
		}
		$this->parameters[$key]	= $value;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
	}
}
