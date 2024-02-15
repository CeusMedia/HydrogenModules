<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_WalletLogo extends View_Helper_Stripe_Abstract
{
	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'div';
	protected $wallet;
	protected string $size			= 'large';

	const SIZE_SMALL		= 'fa-1x';
	const SIZE_MEDIUM		= 'fa-2x';
	const SIZE_LARGE		= 'fa-4x';

	public static function renderStatic( Environment $env, $number, $nodeName = NULL, $nodeClass = NULL ): string
	{
		$instance	= new View_Helper_Stripe_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	public function render(): string
	{
		$icon	= 'fa-money';
		switch( $this->wallet->Currency ){
			case 'EUR':
				$icon	= 'fa-euro';
				break;
			case 'USD':
				$icon	= 'fa-dollar';
				break;
			case 'YEN':
				$icon	= 'fa-yen';
				break;
		}
		$classes	= ['fa fa-fw', $icon, $this->size];
		$image		= HtmlTag::create( 'i', '', ['class' => join( ' ', $classes )] );
		return HtmlTag::create( $this->nodeName, $image, [
			'class'	=> $this->nodeClass,
		] );
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

	public function setSize( string $size ): self
	{
		$this->size	= $size;
		return $this;
	}

	public function setWallet( $wallet ): self
	{
		$this->wallet	= $wallet;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->setSize( self::SIZE_MEDIUM );
	}
}
