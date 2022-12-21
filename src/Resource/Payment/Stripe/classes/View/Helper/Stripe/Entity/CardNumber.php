<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Stripe_Entity_CardNumber extends View_Helper_Stripe_Abstract{

	protected $nodeClass	= NULL;
	protected $nodeName		= 'tt';
	protected $number;

	public function render(){
		$pattern	= '/^([^x]+)(x+)(.+)$/i';
		$replace	= '\\1<small class="muted">\\2</small>\\3';
		$number		= preg_replace( $pattern, $replace, $this->number );
		return HtmlTag::create( $this->nodeName, $number, array(
			'class'	=> $this->nodeClass,
		) );
	}

	static public function renderStatic( Environment $env, $number, $nodeName = NULL, $nodeClass = NULL ){
		$instance	= new View_Helper_Stripe_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}

	public function set( $number ){
		$this->number	= $number;
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
