<?php
class View_Helper_Shop_Tabs{

	protected $backends;
	protected $cartTotal			= 0;
	protected $content;
	protected $current;
	protected $env;
	protected $whiteIcons;

	public function __construct( $env ){
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
	}

	public function __toString(){
		return $this->render();
	}

	public function setCartTotal( $cartTotal ){
		$this->cartTotal	= $cartTotal;
	}

	public function setContent( $content ){
		$this->content	= $content;
	}

	public function setCurrent( $current ){
		$this->current	= $current;
	}

	public function setPaymentBackends( $backends ){
		$this->backends	= $backends;
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

		$tabLabels			= (object) $this->words['tabs'];

		$iconCart			= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->cart, 'class' => 'fa fa-fw fa-shopping-cart' ) );
		$iconCustomer		= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->customer, 'class' => 'fa fa-fw fa-user-o' ) );
		$iconConditions		= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->conditions, 'class' => 'fa fa-fw fa-check-square-o' ) );
		$iconPayment		= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->payment, 'class' => 'fa fa-fw fa-money' ) );
		$iconCheckout		= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->checkout, 'class' => 'fa fa-fw fa-check' ) );
		$iconService		= UI_HTML_Tag::create( 'i', '', array( 'title' => $tabLabels->service, 'class' => 'fa fa-fw fa-star' ) );

		$tabLabels			= (object) $this->words['tabs'];
		foreach( $tabLabels as $key => $value )
			$tabLabels->$key	= UI_HTML_Tag::create( 'span', '&nbsp;'.$value.'&nbsp;', array( 'class' => 'hidden-phone' ) );

		$tabs->add(
			'shop-cart',
			'./shop/cart',
			$iconCart.$tabLabels->cart,
			$this->current === 'shop-cart' ? $this->content : ''
		);
		$tabs->add(
			'shop-customer',
			'./shop/customer',
			$iconCustomer.$tabLabels->customer,
			$this->current === 'shop-customer' ? $this->content : ''
		);
		$tabs->add(
			'shop-conditions',
			'./shop/conditions',
			$iconConditions.$tabLabels->conditions,
			$this->current === 'shop-conditions' ? $this->content : ''
		);
		if( count( $this->backends ) > 1 && $this->cartTotal > 0 )
			$tabs->add(
				'shop-payment',
				'./shop/payment',
				$iconPayment.$tabLabels->payment,
				$this->current === 'shop-payment' ? $this->content : ''
		);
		$tabs->add(
			'shop-checkout',
			'./shop/checkout',
			$iconCheckout.$tabLabels->checkout,
			$this->current === 'shop-checkout' ? $this->content : ''
		);
		$tabs->add(
			'shop-service',
			'./shop/service',
			$iconService.$tabLabels->service,
			$this->current === 'shop-service' ? $this->content : ''
		);

		$tabs->setActive( $this->current ? $this->current : 0 );

		foreach( $disabled as $nr )
			$tabs->disableTab( $nr );
		return $tabs->render();
	}
}
