<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_Entity_CardNumber extends View_Helper_Mangopay_Abstract{

	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'tt';
	protected $number;

	public function render(): string
	{
		$pattern	= '/^([^x]+)(x+)(.+)$/i';
		$replace	= '\\1<small class="muted">\\2</small>\\3';
		$number		= preg_replace( $pattern, $replace, $this->number );
		return HtmlTag::create( $this->nodeName, $number, [
			'class'	=> $this->nodeClass,
		] );
	}

	static public function renderStatic( Environment $env, $number, ?string $nodeName = NULL, ?string $nodeClass = NULL ): string
	{
		$instance	= new View_Helper_Mangopay_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	public function set( $number ): self
	{
		$this->number	= $number;
		return $this;
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
}
