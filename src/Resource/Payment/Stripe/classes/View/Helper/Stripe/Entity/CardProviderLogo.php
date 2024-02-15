<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_CardProviderLogo extends View_Helper_Stripe_Abstract
{
	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'div';
	protected string$provider		= 'Visa';
	protected string$size			= 'large';

	const SIZE_SMALL		= 'small';
	const SIZE_MEDIUM		= 'medium';
	const SIZE_LARGE		= 'large';

	public function render(): string
	{
		$path		= 'images/paymentProviderLogo/'.$this->size.'/';
		$path		.= strtolower( $this->provider ).'-1.png';
		$image		= HtmlTag::create( 'img', NULL, [
			'src'	=> $path,
		] );
		return HtmlTag::create( $this->nodeName, $image, [
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

	public function setNodeClass( string $classNames ): self
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( string $nodeName ): self
	{
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setProvider( string $provider ): self
	{
		$this->provider	= $provider;
		return $this;
	}

	public function setSize( string $size ): self
	{
		$this->size	= $size;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->setSize( self::SIZE_MEDIUM );
	}
}
