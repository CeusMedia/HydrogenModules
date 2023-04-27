<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_BIC extends View_Helper_Stripe_Abstract{

	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'tt';
	protected object $bic;

	public function render(): string
	{
		$parts	= [
			substr( $this->bic, 0, 4 ),
			substr( $this->bic, 4, 2 ),
			substr( $this->bic, 6, 2 ),
			substr( $this->bic, 8, 3 ),
		];
		$label		= implode( ' ', $parts );
		return HtmlTag::create( $this->nodeName, $label, [
			'class'	=> $this->nodeClass,
		] );
	}

	public static function renderStatic( Environment $env, string $iban, ?string $nodeName = NULL, ?string $nodeClass = NULL ): string
	{
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$instance->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$instance->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}

	public function set( object $bic ): self
	{
		$this->bic	= $bic;
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
