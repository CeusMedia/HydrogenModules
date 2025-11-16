<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;

abstract class Mail_Shop_Abstract extends Mail_Abstract
{
	protected ?Entity_Shop_Order $order						= NULL;
	protected Logic_ShopBridge $logicBridge;
	protected Logic_Shop $logicShop;
	protected View_Helper_Shop $helperShop;
	protected View_Helper_Shop_AddressView $helperAddress;
	protected View_Helper_Shop_CartPositions $helperCart;
	protected View_Helper_Shop_OrderFacts $helperOrderFacts;
	protected array $words;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		RangeException			if given order ID is invalid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->logicBridge		= new Logic_ShopBridge( $this->env );
		$this->logicShop		= new Logic_Shop( $this->env );
		$this->helperShop		= new View_Helper_Shop( $this->env );
		$this->helperAddress	= new View_Helper_Shop_AddressView( $this->env );
		$this->helperCart		= new View_Helper_Shop_CartPositions( $this->env );
		$this->helperCart->setDisplay( View_Helper_Shop_CartPositions::DISPLAY_MAIL );
		$this->helperOrderFacts	= new View_Helper_Shop_OrderFacts( $this->env );
		$this->helperOrderFacts->setDisplay( View_Helper_Shop_OrderFacts::DISPLAY_MAIL );
		$this->words			= $this->getWords( 'shop' );

		if( empty( $this->data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );

		$this->order		= $this->logicShop->getOrder( $this->data['orderId'], TRUE );
		foreach( $this->order->positions as $position ){
			$bridge				= $this->logicBridge->getBridgeObject( (int) $position->bridgeId );
			$position->article	= $bridge->get( $position->articleId, $position->quantity );
		}
		$this->helperCart->setPositions( $this->order->positions );
		$this->helperCart->setPaymentBackends( $this->data['paymentBackends'] );
		$this->helperCart->setPaymentBackend( $this->order->paymentMethod );
		$this->helperCart->setDeliveryAddress( $this->order->customer->addressDelivery );

		$this->addThemeStyle( 'module.shop.css' );
		$this->addBodyClass( 'moduleShop' );
		$this->page->setBaseHref( $this->env->url );
	}

	/**
	 *	@param		int			$outputFormat
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		InvalidArgumentException		if order ID in mail data is missing
	 *	@throws		InvalidArgumentException		if order ID in mail data is invalid
	 *	@throws		ReflectionException
	 */
	protected function getContentTemplateData( int $outputFormat ): array
	{
		$this->helperCart->setOutput( $outputFormat );
		$this->helperAddress->setOutput( $outputFormat );
		$this->helperOrderFacts->setOutput( $outputFormat );

		$paymentBackends	= $this->data['paymentBackends'] ?? new Dictionary();
		$paymentBackend		= $paymentBackends->get( $this->order->paymentMethod );

		$panelPayment	= '';
		if( str_contains( static::class, '_Customer_' ) ){
			$extension		= View_Helper_Shop_CartPositions::OUTPUT_HTML === $outputFormat ? 'html' : 'txt';
			$filePayment	= 'mail/shop/customer/ordered/'.$paymentBackend->path.'.'.$extension;
			$languagePath	= $this->env->getLanguage()->getLanguagePath();
			if( file_exists( $languagePath.$filePayment ) )
				$panelPayment	= $this->loadContentFile( $filePayment, [
					'module'	=> $this->env->getConfig()->getAll( 'module.', TRUE ),
					'order'		=> $this->order,
				] );
		}

		return [
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
			'priceTotal'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
			'paymentBackend'	=> $paymentBackend,
			'tableCart'			=> $this->helperCart->render(),
			'addressDelivery'	=> $this->helperAddress->setAddress( $this->order->customer->addressDelivery )->render(),
			'addressBilling'	=> $this->helperAddress->setAddress( $this->order->customer->addressBilling )->render(),
			'orderFacts'		=> $this->helperOrderFacts->setData( $this->data )->render(),
			'panelPayment'		=> $panelPayment,
		];
	}

	/**
	 *	Needs order to be set beforehand.
	 *	@param		string		$subject
	 *	@param		array		$additionalData
	 *	@return		string
	 */
	protected function renderSubject( string $subject, array $additionalData = [] ): string
	{
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$subject	= str_replace( "%orderId%", $this->order->orderId, $subject );
		foreach( $additionalData as $key => $value )
			$subject	= str_replace( "%$key%", $value, $subject );
		return $subject;
	}
}