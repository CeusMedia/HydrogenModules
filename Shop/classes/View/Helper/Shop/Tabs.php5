<?php
class View_Helper_Shop_Tabs{

	protected $current;
	protected $content;
	protected $backends;
	protected $env;
	protected $whiteIcons;

	public function __construct( $env ){
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
	}

	public function __toString(){
		return $this->render();
	}

	public function setPaymentBackends( $backends ){
		$this->backends	= $backends;
	}

	public function setContent( $content ){
		$this->content	= $content;
	}

	public function setCurrent( $current ){
		$this->current	= $current;
	}

	public function setWhiteIcons( $bool ){
		$this->whiteIcons	= $bool;
	}

	public function render(){
		$tabs	= new \CeusMedia\Bootstrap\Tabs( "tabs-cart" );
		$session	= $this->env->getSession();
		$order		= $session->get( 'shop.order' );
		$positions	= $session->get( 'shop.order.positions' );
		$customer	= $session->get( 'shop.order.customer' );
		$disabled	= array(
			'shop-customer',
			'shop-conditions',
			'shop-payment',
			'shop-checkout',
			'shop-service'
		);

		if( count( $positions ) ){
			unset( $disabled[array_search( 'shop-customer', $disabled )] );
			if( $customer ){
				unset( $disabled[array_search( 'shop-conditions', $disabled )] );
				if( $order->rules ){
					unset( $disabled[array_search( 'shop-payment', $disabled )] );
					if( $order->paymentMethod || !count( $this->backends ) )
						unset( $disabled[array_search( 'shop-checkout', $disabled )] );
				}
			}
		}

		$iconCart			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
		$iconCustomer		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user-o' ) );
		$iconConditions		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-square-o' ) );
		$iconCheckout		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
		$iconPayment		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-money' ) );
		$iconService		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );

		$tabLabels			= (object) $this->words['tabs'];

		$tabs->add( 'shop-cart', './shop/cart', $iconCart.'&nbsp;'.$tabLabels->cart.'&nbsp;', $this->content );
		$tabs->add( 'shop-customer', './shop/customer', $iconCustomer.'&nbsp;'.$tabLabels->customer.'&nbsp;', $this->content );
		$tabs->add( 'shop-conditions', './shop/conditions', $iconConditions.'&nbsp;'.$tabLabels->conditions.'&nbsp;', $this->content );
		if( count( $this->backends ) > 1 )
			$tabs->add( 'shop-payment', './shop/payment', $iconPayment.'&nbsp;'.$tabLabels->payment.'&nbsp;', $this->content );
		$tabs->add( 'shop-checkout', './shop/checkout', $iconCheckout.'&nbsp;'.$tabLabels->checkout.'&nbsp;', $this->content );
		$tabs->add( 'shop-service', './shop/service', $iconService.'&nbsp;'.$tabLabels->service.'&nbsp;', $this->content );

		$tabs->setActive( $this->current ? $this->current : 0 );

		foreach( $disabled as $nr )
			$tabs->disableTab( $nr );
		return $tabs->render();
	}
}
