<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_Entity_WalletLogo extends View_Helper_Mangopay_Abstract
{
	public const SIZE_SMALL			= 'fa-1x';
	public const SIZE_MEDIUM		= 'fa-2x';
	public const SIZE_LARGE			= 'fa-4x';

	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'div';
	protected $wallet;
	protected string $size			= 'large';

	public static function renderStatic( Environment $env, $number, $nodeName = NULL, $nodeClass = NULL ): string
	{
		$instance	= new View_Helper_Mangopay_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	public function render(): string
	{
		$icon = match( $this->wallet->Currency ){
			'EUR'	=> 'fa-euro',
			'USD'	=> 'fa-dollar',
			'YEN'	=> 'fa-yen',
			default	=> 'fa-money',
		};
		$classes	= ['fa fa-fw', $icon, $this->size];
		$image		= HtmlTag::create( 'i', '', ['class' => join( ' ', $classes )] );
		return HtmlTag::create( $this->nodeName, $image, [
			'class'	=> $this->nodeClass,
		] );
	}

	public function setNodeClass( $classNames ): static
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( $nodeName ): static
	{
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setSize( $size ): static
	{
		$this->size	= $size;
		return $this;
	}

	public function setWallet( $wallet ): static
	{
		$this->wallet	= $wallet;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->setSize( self::SIZE_MEDIUM );
	}
}
