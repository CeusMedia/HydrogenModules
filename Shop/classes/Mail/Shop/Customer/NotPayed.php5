<?php
class Mail_Shop_Customer_NotPayed extends Mail_Abstract{

	protected $order;
	protected $customer;
	protected $positions;
	protected $logicBridge;
	protected $logicShop;
	protected $helperAddress;
	protected $helperCart;

	protected function generate( $data = array() ){
		$this->logicBridge		= new Logic_ShopBridge( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->helperAddress	= new View_Helper_Shop_AddressView( $this->env );
		$this->helperCart		= new View_Helper_Shop_CartPositions( $this->env );
		$this->helperCart->setDisplay( View_Helper_Shop_CartPositions::DISPLAY_MAIL );
		$this->words			= $this->getWords( 'shop' );

		if( empty( $data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );

		$this->order		= $this->logicShop->getOrder( $data['orderId'] );
		if( !$this->order )
			throw new InvalidArgumentException( 'Invalid order ID' );
		$this->customer		= $this->logicShop->getCustomer( $this->order->userId, TRUE );
//		print_m( $this->customer );die;
		$this->positions	= $this->logicShop->getOrderPositions( $this->order->orderId );
		foreach( $this->positions as $nr => $position ){
			$bridge				= $this->logicBridge->getBridgeObject( (int) $position->bridgeId );
			$position->article	= $bridge->get( $position->articleId, $position->quantity );
		}
		$this->helperCart->setPositions( $this->positions );

		$wordsMail	= (object) $this->words['mail-customer-not-payed'];
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $wordsMail->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$subject	= str_replace( "%orderId%", $this->order->orderId, $subject );
		$this->setSubject( $subject );

		$this->addTextBody( $this->renderText( $data ) );
		$this->addHtmlBody( $this->renderHtml( $data ) );
	}

	public function renderHtml( $data ){
		$this->helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_HTML );
		$this->helperAddress->setOutput( View_Helper_Shop_AddressView::OUTPUT_HTML );

		$paymentBackend	= NULL;
		foreach( $data['paymentBackends'] as $item )
			if( $item->key === $this->order->paymentMethod )
				$paymentBackend	= $item;

		$helperShop	= new View_Helper_Shop( $this->env );

		$body	= $this->view->loadContentFile( 'mail/shop/manager/not_payed.html', array(
			'orderDate'			=> date( 'd.m.Y', $this->order->modifiedAt ),
			'orderTime'			=> date( 'H:i:s', $this->order->modifiedAt ),
			'orderStatus'		=> $this->words['statuses-order'][$this->order->status],
			'orderStatusTitle'	=> $this->words['statuses-order-title'][$this->order->status],
			'date'				=> array( 'year' => date( 'Y' ), 'month' => date( 'm' ), 'day' => date( 'd' ) ),
			'config'			=> $this->env->getConfig()->getAll( 'module.shop.' ),
			'env'				=> array( 'domain' => $this->env->host ),
			'main'				=> (object) $this->getWords( 'main', 'main' ),
			'words'				=> $this->words,
			'order'				=> $this->order,
			'priceTotal'		=> $helperShop->formatPrice( $this->order->priceTaxed ),
			'paymentBackend'	=> $paymentBackend,
			'tableCart'			=> $this->helperCart->render(),
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->customer->addressBilling )->render(),
		) );
		$this->addThemeStyle( 'module.shop.css' );
		$this->addBodyClass( 'moduleShop' );
		$this->page->setBaseHref( $this->env->url );
		return $body;
	}

	public function renderText( $data ){
		$this->helperCart->setOutput( View_Helper_Shop_CartPositions::OUTPUT_TEXT );
		$this->helperAddress->setOutput( View_Helper_Shop_AddressView::OUTPUT_TEXT );
		$templateData	= array(
			'date'			=> date( 'd.m.Y' ),
			'time'			=> date( 'H:i:s' ),
			'config'		=> $this->env->getConfig()->getAll( 'module.shop.' ),
			'env'			=> array( 'domain' => $this->env->host ),
			'main'			=> (object) $this->getWords( 'main', 'main' ),
			'words'			=> (object) $this->getWords( 'shop', 'mail-customer-not-payed' ),
			'customer'		=> $this->customer,
			'tableCart'		=> $this->helperCart->render(),
		);
		return $this->view->loadContentFile( 'mail/shop/customer/not_payed.txt', $templateData );
	}
}
