<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Mangopay_Entity_IBAN extends View_Helper_Mangopay_Abstract
{
	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'tt';
	protected string $iban;

	public function render(): string
	{
		$parts		= str_split( trim( $this->iban ), 4 );
		$label		= implode( ' ', $parts );
		return HtmlTag::create( $this->nodeName, $label, [
			'class'	=> $this->nodeClass,
		] );
	}

	public static function renderStatic( Environment $env, $iban, $nodeName = NULL, $nodeClass = NULL ): string
	{
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}

	public function set( string $iban ): self
	{
		$this->iban	= $iban;
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
