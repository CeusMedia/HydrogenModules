<?php
class Mail_Shop_Manager_NotPayed extends Mail_Abstract
{
	protected $order;
	protected $logicBridge;
	protected $logicShop;
	protected $helperAddress;
	protected $helperCart;

	public function renderHtml( array $data ): string
	{
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
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->order->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->order->customer->addressBilling )->render(),
		) );
		$this->addThemeStyle( 'module.shop.css' );
		$this->addBodyClass( 'moduleShop' );
		$this->page->setBaseHref( $this->env->url );
		return $body;
	}

	/**
	 *	@todo		implement
	 */
	public function renderText( array $data ): string
	{
		return '';
	}

	protected function generate( $data = array() )
	{
		$this->logicBridge		= new Logic_ShopBridge( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->helperAddress	= new View_Helper_Shop_AddressView( $this->env );
		$this->helperCart		= new View_Helper_Shop_CartPositions( $this->env );
		$this->helperCart->setDisplay( View_Helper_Shop_CartPositions::DISPLAY_MAIL );
		$this->words			= $this->getWords( 'shop' );

		if( empty( $data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );

		$this->order		= $this->logicShop->getOrder( $data['orderId'], TRUE );
		if( !$this->order )
			throw new InvalidArgumentException( 'Invalid order ID' );
		foreach( $this->order->positions as $nr => $position ){
			$bridge				= $this->logicBridge->getBridgeObject( (int) $position->bridgeId );
			$position->article	= $bridge->get( $position->articleId, $position->quantity );
		}
		$this->helperCart->setPositions( $this->order->positions );
		$this->helperCart->setDeliveryAddress( $this->order->customer->addressDelivery );

		$wordsMail	= (object) $this->words['mail-manager-not-payed'];
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $wordsMail->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$subject	= str_replace( "%orderId%", $this->order->orderId, $subject );
		$this->setSubject( $subject );
//		$this->setText( $this->renderText( $data ) );
		$this->setHtml( $this->renderHtml( $data ) );
	}
}
