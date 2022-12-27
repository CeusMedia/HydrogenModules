<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Stripe_Entity_Wallet extends View_Helper_Stripe_Abstract{

	protected $nodeClass	= NULL;
	protected $nodeName		= 'span';
	protected $wallet;

	public function render(){
		$helper		= new View_Helper_Stripe_Entity_Money( $this->env );
		$helper->setFormat( View_Helper_Stripe_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
		$helper->setNumberFormat( View_Helper_Stripe_Entity_Money::NUMBER_FORMAT_COMMA );
		$helper->set( $this->wallet->Balance );
		$icon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-briefcase'] );
		$balance	= HtmlTag::create( 'small', '('.$helper.')', ['class' => 'muted'] );
		$label		= $icon.' '.$this->wallet->Description.' '.$balance;
		return HtmlTag::create( $this->nodeName, $label, ['class' => $this->nodeClass] );
	}

	public function set( $wallet ){
		$this->wallet	= $wallet;
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
