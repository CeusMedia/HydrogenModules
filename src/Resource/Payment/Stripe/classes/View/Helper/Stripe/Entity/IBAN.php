<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_IBAN extends View_Helper_Stripe_Abstract{

	protected $nodeClass	= NULL;
	protected $nodeName		= 'tt';
	protected $iban;

	public function render(){
		$parts		= str_split( trim( $this->iban ), 4 );
		$label		= implode( ' ', $parts );
		return HtmlTag::create( $this->nodeName, $label, [
			'class'	=> $this->nodeClass,
		] );
	}

	static public function renderStatic( Environment $env, $iban, $nodeName = NULL, $nodeClass = NULL ){
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}

	public function set( $iban ){
		$this->iban	= $iban;
		return $this;
	}

	public function setNodeClass( $classNames ){
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( $nodeName ){
		$this->nodeName	= $nodeName;
		return $this;
	}
}
