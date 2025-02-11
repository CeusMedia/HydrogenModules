<?php

use CeusMedia\Bootstrap\Nav\Tabs as BootstrapTabsNav;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_Tabs
{
	protected Environment $env;
	protected array $words;
	protected Model_Shop_Payment_BackendRegister $backends;
	protected float $cartTotal			= .0;
	protected string $content			= '';
	protected ?string $current			= NULL;
	protected bool $whiteIcons			= FALSE;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
	}

	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		$tabs		= new BootstrapTabsNav( "tabs-cart" );
//		$session	= $this->env->getSession();
		$modelCart	= new Model_Shop_Cart( $this->env );
		$positions	= $modelCart->get( 'positions' );
		$disabled	= [
			'shop-customer',
			'shop-conditions',
			'shop-payment',
			'shop-checkout',
			'shop-service'
		];

		if( is_array( $positions ) && count( $positions ) ){
			unset( $disabled[array_search( 'shop-customer', $disabled )] );
			if( $modelCart->get( 'orderStatus' ) >= Model_Shop_Order::STATUS_AUTHENTICATED ){
				unset( $disabled[array_search( 'shop-conditions', $disabled )] );
				if( $modelCart->get( 'acceptRules' ) ){
					unset( $disabled[array_search( 'shop-payment', $disabled )] );
					if( $modelCart->get( 'paymentMethod' ) || !count( $this->backends->getAll() ) )
						unset( $disabled[array_search( 'shop-checkout', $disabled )] );
				}
			}
		}

		$tabLabels			= (object) $this->words['tabs'];

		$iconCart			= HtmlTag::create( 'i', '', ['title' => $tabLabels->cart, 'class' => 'fa fa-fw fa-shopping-cart'] );
		$iconCustomer		= HtmlTag::create( 'i', '', ['title' => $tabLabels->customer, 'class' => 'fa fa-fw fa-user-o'] );
		$iconConditions		= HtmlTag::create( 'i', '', ['title' => $tabLabels->conditions, 'class' => 'fa fa-fw fa-check-square-o'] );
		$iconPayment		= HtmlTag::create( 'i', '', ['title' => $tabLabels->payment, 'class' => 'fa fa-fw fa-money'] );
		$iconCheckout		= HtmlTag::create( 'i', '', ['title' => $tabLabels->checkout, 'class' => 'fa fa-fw fa-check'] );
		$iconService		= HtmlTag::create( 'i', '', ['title' => $tabLabels->service, 'class' => 'fa fa-fw fa-star'] );

		foreach( $tabLabels as $key => $value )
			$tabLabels->$key	= HtmlTag::create( 'span', '&nbsp;'.$value.'&nbsp;', ['class' => 'hidden-phone'] );

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
		if( count( $this->backends->getAll() ) > 1 && $this->cartTotal > 0 )
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

		$tabs->setActive( $this->current ?: 0 );

		foreach( $disabled as $nr )
			$tabs->disableTab( $nr );
		return $tabs->render();
	}

	public function setCartTotal( float $cartTotal ): self
	{
		$this->cartTotal	= $cartTotal;
		return $this;
	}

	public function setContent( string $content ): self
	{
		$this->content	= $content;
		return $this;
	}

	public function setCurrent( $current ): self
	{
		$this->current	= $current;
		return $this;
	}

	public function setPaymentBackends(Model_Shop_Payment_BackendRegister $backends ): self
	{
		$this->backends	= $backends;
		return $this;
	}

	public function setWhiteIcons( bool $bool ): self
	{
		$this->whiteIcons	= $bool;
		return $this;
	}
}
