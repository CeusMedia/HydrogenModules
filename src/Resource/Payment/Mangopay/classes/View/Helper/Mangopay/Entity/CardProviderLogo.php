<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_Entity_CardProviderLogo extends View_Helper_Mangopay_Abstract
{
	protected $nodeClass	= NULL;
	protected $nodeName		= 'div';
	protected $provider		= 'Visa';
	protected $size			= 'large';

	const SIZE_SMALL		= 'small';
	const SIZE_MEDIUM		= 'medium';
	const SIZE_LARGE		= 'large';

	public static function renderStatic( Environment $env, $number, $nodeName = NULL, $nodeClass = NULL )
	{
		$instance	= new View_Helper_Mangopay_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	public function render()
	{
		$path		= 'images/paymentProviderLogo/'.$this->size.'/';
		$path		.= strtolower( $this->provider ).'-1.png';
		$image		= HtmlTag::create( 'img', NULL, array(
			'src'	=> $path,
		) );
		return HtmlTag::create( $this->nodeName, $image, array(
			'class'	=> $this->nodeClass,
		) );
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
