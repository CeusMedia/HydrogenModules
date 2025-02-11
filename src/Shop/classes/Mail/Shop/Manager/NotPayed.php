<?php

class Mail_Shop_Manager_NotPayed extends Mail_Abstract
{
	protected ?object $order								= NULL;
	protected Logic_ShopBridge $logicBridge;
	protected Logic_Shop $logicShop;
	protected View_Helper_Shop_AddressView $helperAddress;
	protected View_Helper_Shop_CartPositions $helperCart;
	protected array $words;

	protected function __onInit(): void
	{
		$this->logicBridge		= new Logic_ShopBridge( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->helperAddress	= new View_Helper_Shop_AddressView( $this->env );
		$this->helperCart		= new View_Helper_Shop_CartPositions( $this->env );
		$this->helperCart->setDisplay( View_Helper_Shop_CartPositions::DISPLAY_MAIL );
		$this->words			= $this->getWords( 'shop' );
	}

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		if( empty( $this->data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );

		$this->order		= $this->logicShop->getOrder( $this->data['orderId'], TRUE );
		if( !$this->order )
			throw new InvalidArgumentException( 'Invalid order ID' );
		foreach( $this->order->positions as $nr => $position ){
			$bridge				= $this->logicBridge->getBridgeObject( (int) $position->bridgeId );
			$position->article	= $bridge->get( $position->articleId, $position->quantity );
		}
		$this->helperCart->setPositions( $this->order->positions );
		$this->helperCart->setPaymentBackend( $this->order->paymentMethod );
		$this->helperCart->setDeliveryAddress( $this->order->customer->addressDelivery );

		$wordsMail	= (object) $this->words['mail-manager-not-payed'];
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $wordsMail->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$subject	= str_replace( "%orderId%", $this->order->orderId, $subject );
		$this->setSubject( $subject );
//		$this->setText( $this->renderText() );
		$this->setHtml( $this->renderHtml() );
		return $this;
	}

	protected function renderHtml(): string
	{
		$this->helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML );
		$this->helperAddress->setOutput( View_Helper_Shop_AddressView::OUTPUT_HTML );

		$paymentBackend	= NULL;
		foreach( $this->data['paymentBackends']->getAll() as $item )
			if( $item->key === $this->order->paymentMethod )
				$paymentBackend	= $item;

		$helperShop	= new View_Helper_Shop( $this->env );

		$body	= $this->loadContentFile( 'mail/shop/manager/not_payed.html', [
			'orderDate'			=> date( 'd.m.Y', $this->order->modifiedAt ),
			'orderTime'			=> date( 'H:i:s', $this->order->modifiedAt ),
			'orderStatus'		=> $this->words['statuses-order'][$this->order->status],
			'orderStatusTitle'	=> $this->words['statuses-order-title'][$this->order->status],
			'date'				=> ['year' => date( 'Y' ), 'month' => date( 'm' ), 'day' => date( 'd' )],
			'config'			=> $this->env->getConfig()->getAll( 'module.shop.' ),
			'env'				=> ['domain' => $this->env->host],
			'main'				=> (object) $this->getWords( 'main', 'main' ),
			'words'				=> $this->words,
			'order'				=> $this->order,
			'priceTotal'		=> $helperShop->formatPrice( $this->order->priceTaxed ),
			'paymentBackend'	=> $paymentBackend,
			'tableCart'			=> $this->helperCart->render(),
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->order->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->order->customer->addressBilling )->render(),
		] ) ?? '';
		$this->addThemeStyle( 'module.shop.css' );
		$this->addBodyClass( 'moduleShop' );
		$this->page->setBaseHref( $this->env->url );
		return $body;
	}

	/**
	 *	@todo		implement
	 */
	protected function renderText(): string
	{
		return '';
	}
}
