<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_BIC extends View_Helper_Stripe_Abstract{

	protected $nodeClass	= NULL;
	protected $nodeName		= 'tt';
	protected $bic;

	public function render(){
		$parts	= array(
			substr( $this->bic, 0, 4 ),
			substr( $this->bic, 4, 2 ),
			substr( $this->bic, 6, 2 ),
			substr( $this->bic, 8, 3 ),
		);
		$label		= implode( ' ', $parts );
		return UI_HTML_Tag::create( $this->nodeName, $label, array(
			'class'	=> $this->nodeClass,
		) );
	}

	static public function renderStatic( Environment $env, $iban, $nodeName = NULL, $nodeClass = NULL ){
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}

	public function set( $bic ){
		$this->bic	= $bic;
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
?>
