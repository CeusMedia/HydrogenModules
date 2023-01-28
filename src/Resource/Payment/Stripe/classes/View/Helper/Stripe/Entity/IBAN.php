<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_IBAN extends View_Helper_Stripe_Abstract
{
	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'tt';
	protected string $iban			= '';

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$parts		= str_split( trim( $this->iban ), 4 );
		$label		= implode( ' ', $parts );
		return HtmlTag::create( $this->nodeName, $label, [
			'class'	=> $this->nodeClass,
		] );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		$iban
	 *	@param		string|NULL		$nodeName
	 *	@param		string|NULL		$nodeClass
	 *	@return		string
	 */
	public static function renderStatic( Environment $env, $iban, ?string $nodeName = NULL, ?string $nodeClass = NULL ): string
	{
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}

	/**
	 *	@param		string		$iban
	 *	@return		self
	 */
	public function set( string $iban ): self
	{
		$this->iban	= $iban;
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
