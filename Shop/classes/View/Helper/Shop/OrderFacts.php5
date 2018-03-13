<?php
class View_Helper_Shop_OrderFacts{

	const DISPLAY_UNKNOWN			= 0;
	const DISPLAY_BROWSER			= 1;
	const DISPLAY_MAIL				= 2;

	const OUTPUT_UNKNOWN			= 0;
	const OUTPUT_TEXT				= 1;
	const OUTPUT_HTML				= 2;

	protected $bridge;
	protected $changeable;
	protected $env;
	protected $data;
	protected $helperShop;
	protected $paymentBackend;
	protected $display				= self::DISPLAY_BROWSER;
	protected $output				= self::OUTPUT_HTML;

	public function __construct( $env ){
		$this->env			= $env;
		$this->config		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->words		= $this->env->getLanguage()->getWords( 'shop' );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->helperShop	= new View_Helper_Shop( $this->env );
	}

	public function render(){
		switch( $this->output ){
			case self::OUTPUT_HTML:
				return $this->renderAsHtml();
			case self::OUTPUT_TEXT:
				return $this->renderAsText();
		}
	}

	public function renderAsHtml(){
		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( $this->words['panel-facts'] );
		$helperFacts->add( 'date', date( 'd.m.Y', $this->order->modifiedAt ) );
		$helperFacts->add( 'time', date( 'H:i:s', $this->order->modifiedAt ) );
		$helperFacts->add( 'price', $this->helperShop->formatPrice( $this->order->priceTaxed ) );
		$helperFacts->add( 'payment', $this->paymentBackend->title );
		$helperFacts->add( 'orderId', $this->order->orderId );
		return $helperFacts->render();
	}

	protected function renderAsText(){
		$words			= (object) $this->words['panel-facts'];
		$helperText		= new View_Helper_Mail_Text();
		$list			= array();
		$list[]			= $helperText->line( "=", 78 );
		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( $this->words['panel-facts'] );
		$helperFacts->add( 'date', date( 'd.m.Y', $this->order->modifiedAt ) );
		$helperFacts->add( 'time', date( 'H:i:s', $this->order->modifiedAt ) );
		$helperFacts->add( 'price', $this->helperShop->formatPrice( $this->order->priceTaxed, TRUE, FALSE ) );
		$helperFacts->add( 'payment', $this->paymentBackend->title );
		$helperFacts->add( 'orderId', $this->order->orderId );
		$list[]	= $helperFacts->renderAsText();
		$list[]	= $helperText->line( "-", 78 );
		return join( PHP_EOL, $list );
	}

	public function setData( $data ){
		$this->data	= (object) $data;
		if( empty( $data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );
		$this->order		= $this->logicShop->getOrder( $data['orderId'] );
		if( !$this->order )
			throw new InvalidArgumentException( 'Invalid order ID' );
		$paymentBackend	= NULL;
		foreach( $data['paymentBackends'] as $item )
			if( $item->key === $this->order->paymentMethod )
				$this->paymentBackend	= $item;
		$this->facts		= array(
			'date'		=> date( 'd.m.Y', $this->order->modifiedAt ),
			'time'		=> date( 'H:i:s', $this->order->modifiedAt ),
			'price'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
			'payment'	=> $this->paymentBackend->title,
			'orderId'	=> $this->order->orderId,
		);
		return $this;
	}

	public function setDisplay( $display ){
		if( !in_array( (int) $display, array( self::DISPLAY_BROWSER, self::DISPLAY_MAIL	) ) )
			throw new InvalidArgumentException( 'Invalid display format' );
		$this->display		= $display;
		return $this;
	}

	public function setOutput( $format ){
		$formats	= array( self::OUTPUT_HTML, self::OUTPUT_TEXT );
		if( !in_array( (int) $format, $formats ) )
			throw new InvalidArgumentException( 'Invalid output format' );
		$this->output		= (int) $format;
		return $this;
	}
}
