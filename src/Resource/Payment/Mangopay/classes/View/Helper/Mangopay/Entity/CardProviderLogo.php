<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_Entity_CardProviderLogo extends View_Helper_Mangopay_Abstract
{
	public const SIZE_SMALL			= 'small';
	public const SIZE_MEDIUM		= 'medium';
	public const SIZE_LARGE			= 'large';

	protected ?string $nodeClass	= NULL;

	protected string $nodeName		= 'div';

	protected string $provider		= 'Visa';

	protected string $size			= 'large';

	public static function renderStatic( Environment $env, $number, ?string $nodeName = NULL, ?string $nodeClass = NULL )
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
		$path		= 'images/paymentProviderLogo/'.$this->size.'/';
		$path		.= strtolower( $this->provider ).'-1.png';
		$image		= HtmlTag::create( 'img', NULL, [
			'src'	=> $path,
		] );
		return HtmlTag::create( $this->nodeName, $image, [
			'class'	=> $this->nodeClass,
		] );
	}

	public function setNodeClass( $classNames )
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( $nodeName )
	{
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setProvider( $provider )
	{
		$this->provider	= $provider;
		return $this;
	}

	public function setSize( $size ){
		$this->size	= $size;
		return $this;
	}

	protected function __onInit(): void
	{
		$this->setSize( self::SIZE_MEDIUM );
	}
}
