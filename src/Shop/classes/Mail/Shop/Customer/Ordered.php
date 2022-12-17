<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

class Mail_Shop_Customer_Ordered extends Mail_Abstract
{
	protected ?object $order								= NULL;
	protected Logic_ShopBridge $logicBridge;
	protected Logic_Shop $logicShop;
	protected View_Helper_Shop_AddressView $helperAddress;
	protected View_Helper_Shop_CartPositions $helperCart;
	protected View_Helper_Shop $helperShop;
	protected View_Helper_Shop_OrderFacts $helperOrderFacts;
	protected array $words;

	protected function __onInit(): void
	{
		$this->logicBridge		= new Logic_ShopBridge( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->helperAddress	= new View_Helper_Shop_AddressView( $this->env );
		$this->helperCart		= new View_Helper_Shop_CartPositions( $this->env );
		$this->helperCart->setDisplay( View_Helper_Shop_CartPositions::DISPLAY_MAIL );
		$this->helperShop		= new View_Helper_Shop( $this->env );
		$this->helperOrderFacts	= new View_Helper_Shop_OrderFacts( $this->env );
		$this->helperOrderFacts->setDisplay( View_Helper_Shop_OrderFacts::DISPLAY_MAIL );
		$this->words			= $this->getWords( 'shop' );

		/* hack: empty view instance with casted environment, will break if cli runtime (job context)
		/** @todo replace this hack by a better general solution */
		/** @var WebEnvironment $env */
		$env		= $this->env;
		$this->view	= new View( $env );
	}

	/**
	 *	@return		self
	 *	@throws		RangeException
	 *	@throws		InvalidArgumentException
	 */
	protected function generate(): self
	{
		if( empty( $this->data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );

		$this->order		= $this->logicShop->getOrder( $this->data['orderId'], TRUE );
		if( !$this->order )
			throw new RangeException( 'Invalid order ID' );
		foreach( $this->order->positions as $nr => $position ){
			$bridge				= $this->logicBridge->getBridgeObject( (int) $position->bridgeId );
			$position->article	= $bridge->get( $position->articleId, $position->quantity );
		}
		$this->helperCart->setPositions( $this->order->positions );
		$this->helperCart->setDeliveryAddress( $this->order->customer->addressDelivery );

		$wordsMail	= (object) $this->words['mail-customer-ordered'];
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $wordsMail->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$subject	= str_replace( "%orderId%", $this->order->orderId, $subject );
		$this->setSubject( $subject );
		$this->setText( $this->renderText() );
		$this->setHtml( $this->renderHtml() );
		return $this;
	}

	protected function renderHtml(): string
	{
		$this->helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML );
		$this->helperAddress->setOutput( View_Helper_Shop_AddressView::OUTPUT_HTML );
		$this->helperOrderFacts->setOutput( View_Helper_Shop_OrderFacts::OUTPUT_HTML );

		$paymentBackend	= NULL;
		foreach( $this->data['paymentBackends'] as $item )
			if( $item->key === $this->order->paymentMethod )
				$paymentBackend	= $item;

//		$this->env->getModules()->callHook( 'Shop', 'renderServicePanels', $this, $this->data );

		$panelPayment	= '';
		$filePayment	= 'mail/shop/customer/ordered/'.$paymentBackend->path.'.html';

		if( $this->view->hasContentFile( $filePayment ) )
			$panelPayment	= $this->view->loadContentFile( $filePayment, [
				'module'		=> $this->env->getConfig()->getAll( 'module.', TRUE ),
				'order'		=> $this->order,
			] );

		$body	= $this->view->loadContentFile( 'mail/shop/customer/ordered.html', [
			'orderDate'			=> date( 'd.m.Y', $this->order->modifiedAt ),
			'orderTime'			=> date( 'H:i:s', $this->order->modifiedAt ),
			'date'				=> ['year' => date( 'Y' ), 'month' => date( 'm' ), 'day' => date( 'd' )],
			'config'			=> $this->env->getConfig()->getAll( 'module.shop.' ),
			'env'				=> ['domain' => $this->env->host],
			'main'				=> (object) $this->getWords( 'main', 'main' ),
			'words'				=> $this->words,
			'order'				=> $this->order,
			'customer'			=> $this->order->customer,
			'priceTotal'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
			'paymentBackend'	=> $paymentBackend,
			'tableCart'			=> $this->helperCart->render(),
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->order->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->order->customer->addressBilling )->render(),
			'orderFacts'		=> $this->helperOrderFacts->setData( $this->data )->render(),
			'panelPayment'		=> $panelPayment,
		] );
		$this->addThemeStyle( 'module.shop.css' );
		$this->addBodyClass( 'moduleShop' );
		$this->page->setBaseHref( $this->env->url );
		return $body;
	}

	protected function renderText(): string
	{
		$this->helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_TEXT );
		$this->helperAddress->setOutput( View_Helper_Shop_AddressView::OUTPUT_TEXT );
		$this->helperOrderFacts->setOutput( View_Helper_Shop_OrderFacts::OUTPUT_TEXT );

		$paymentBackend	= NULL;
		foreach( $this->data['paymentBackends'] as $item )
			if( $item->key === $this->order->paymentMethod )
				$paymentBackend	= $item;

		$panelPayment	= '';
		$filePayment	= 'mail/shop/customer/ordered/'.$paymentBackend->path.'.txt';
		if( $this->view->hasContentFile( $filePayment ) )
			$panelPayment	= $this->view->loadContentFile( $filePayment, [
				'module'	=> $this->env->getConfig()->getAll( 'module.', TRUE ),
				'order'		=> $this->order,
			] );

		$templateData	= [
			'orderDate'			=> date( 'd.m.Y', $this->order->modifiedAt ),
			'orderTime'			=> date( 'H:i:s', $this->order->modifiedAt ),
			'config'			=> $this->env->getConfig()->getAll( 'module.shop.' ),
			'env'				=> ['domain' => $this->env->host],
			'main'				=> (object) $this->getWords( 'main', 'main' ),
			'words'				=> $this->words,
			'order'				=> $this->order,
			'customer'			=> $this->order->customer,
			'priceTotal'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
			'paymentBackend'	=> $paymentBackend,
			'tableCart'			=> $this->helperCart->render(),
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->order->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->order->customer->addressBilling )->render(),
			'orderFacts'		=> $this->helperOrderFacts->setData( $this->data )->render(),
			'panelPayment'		=> $panelPayment,
		];
		return $this->view->loadContentFile( 'mail/shop/customer/ordered.txt', $templateData );
	}
}

class Mail_Shop_Order_Customer extends Mail_Shop_Customer_Ordered
{
}
