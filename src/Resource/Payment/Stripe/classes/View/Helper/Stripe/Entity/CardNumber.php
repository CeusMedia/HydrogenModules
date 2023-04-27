<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_CardNumber extends View_Helper_Stripe_Abstract{

	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'tt';
	protected string $number		= '';

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$pattern	= '/^([^x]+)(x+)(.+)$/i';
		$replace	= '\\1<small class="muted">\\2</small>\\3';
		$number		= preg_replace( $pattern, $replace, $this->number );
		return HtmlTag::create( $this->nodeName, $number, [
			'class'	=> $this->nodeClass,
		] );
	}

	public static function renderStatic( Environment $env, $number, ?string $nodeName = NULL, ?string $nodeClass = NULL ): string
	{
		$instance	= new View_Helper_Stripe_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	/**
	 *	@param		string		$number
	 *	@return		self
	 */
	public function set( string $number ): self
	{
		$this->number	= $number;
		return $this;
	}

	/**
	 *	@param		string		$classNames
	 *	@return		self
	 */
	public function setNodeClass( string $classNames ): self
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	/**
	 *	@param		string		$nodeName
	 *	@return		self
	 */
	public function setNodeName( string $nodeName ): self
	{
		$this->nodeName	= $nodeName;
		return $this;
	}
}
