<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\ADT\URL;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_URL extends View_Helper_Mangopay_Abstract
{
	protected HttpRequest $request;
	protected URL|string|NULL $url	= NULL;
	protected array $parameters		= [];

	public static function renderStatic( Environment $env, $url, $forwardTo = TRUE, $backwardTo = TRUE, $from = TRUE ): string
	{
		$instance	= new self( $env );
		$instance->set( $url );
		$instance->setForwardTo( $forwardTo );
		$instance->setBackwardTo( $backwardTo );
		$instance->setFrom( $from );
		return $instance->render();
	}

	public function render(): string
	{
		$param		= [];
		foreach( $this->parameters as $key => $value )
			if( !in_array( $value, [FALSE, NULL] ) )
				if( strlen( trim( $value ) ) )
					$param[$key]	= (string) $value;

		$param		= $param ? '?'.http_build_query( $param, NULL, '&' ) : '';
		return $this->url.$param;
	}

	public function set( URL|string $url ): self
	{
		$this->url	= $url;
		return $this;
	}

	public function setBackwardTo( string|bool|NULL $path = NULL ): self
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'backwardTo' );
		$this->setParameter( 'backwardTo', (string) $path, TRUE );
		return $this;
	}

	public function setForwardTo( string|bool|NULL $path = NULL ): self
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'forwardTo' );
		$this->setParameter( 'forwardTo', (string) $path, TRUE );
		return $this;
	}

	public function setFrom( string|bool|NULL $path = NULL ): self
	{
		if( $path === TRUE )
			$path	= $this->env->getRequest()->get( 'from' );
		$this->setParameter( 'from', (string) $path, TRUE );
		return $this;
	}

	public function setParameter( string $key, $value, bool $override = FALSE, bool $strict = TRUE ): self
	{
		if( !strlen( trim( $key ) ) )
			throw new DomainException( 'Parameter key cannot be empty' );
		if( array_key_exists( $key, $this->parameters ) ){
			if( !$override ){
				if( $strict )
					throw new RangeException( 'Parameter with key "'.$key.'" is already set' );
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
