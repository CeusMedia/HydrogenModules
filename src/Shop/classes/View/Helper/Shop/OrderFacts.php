<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_OrderFacts
{
	public const DISPLAY_UNKNOWN		= 0;
	public const DISPLAY_BROWSER		= 1;
	public const DISPLAY_MAIL			= 2;

	public const DISPLAYS				= [
		self::DISPLAY_UNKNOWN,
		self::DISPLAY_BROWSER,
		self::DISPLAY_MAIL,
	];

	public const OUTPUT_UNKNOWN			= 0;
	public const OUTPUT_TEXT			= 1;
	public const OUTPUT_HTML			= 2;

	public const OUTPUTS				= [
		self::OUTPUT_UNKNOWN,
		self::OUTPUT_TEXT,
		self::OUTPUT_HTML,
	];

	protected Environment $env;
	protected Dictionary $config;
	protected Logic_ShopBridge $logicBridge;
	protected Logic_Shop $logicShop;
	protected View_Helper_Shop $helperShop;
	protected int $display				= self::DISPLAY_BROWSER;
	protected int $output				= self::OUTPUT_HTML;
	protected array $words;
	protected ?object $paymentBackend	= NULL;
	protected ?object $order			= NULL;
	protected array $facts				= [];

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->config		= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->words		= $this->env->getLanguage()->getWords( 'shop' );
		$this->logicBridge	= new Logic_ShopBridge( $this->env );
		$this->logicShop	= new Logic_Shop( $this->env );
		$this->helperShop	= new View_Helper_Shop( $this->env );
	}

	public function render(): string
	{
		if( self::OUTPUT_HTML === $this->output )
			return $this->renderAsHtml();
		return $this->renderAsText();
	}

	/**
	 *	@param		array		$data
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setData( array $data ): self
	{
//		$this->data	= (object) $data;
		if( empty( $data['orderId'] ) )
			throw new InvalidArgumentException( 'Missing order ID in mail data' );
		$this->order		= $this->logicShop->getOrder( $data['orderId'] );
		if( !$this->order )
			throw new InvalidArgumentException( 'Invalid order ID' );
		$this->paymentBackend	= NULL;
		foreach( $data['paymentBackends']->getAll() as $item )
			if( $item->key === $this->order->paymentMethod )
				$this->paymentBackend	= $item;
		$this->facts		= [
			'date'		=> date( 'd.m.Y', $this->order->modifiedAt ),
			'time'		=> date( 'H:i:s', $this->order->modifiedAt ),
			'price'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
			'payment'	=> $this->paymentBackend->title,
			'orderId'	=> $this->order->orderId,
		];
		return $this;
	}

	public function setDisplay( int $display ): self
	{
		if( !in_array( $display, [self::DISPLAY_BROWSER, self::DISPLAY_MAIL], TRUE ) )
			throw new InvalidArgumentException( 'Invalid display format' );
		$this->display		= $display;
		return $this;
	}

	public function setOutput( int $format ): self
	{
		$formats	= [self::OUTPUT_HTML, self::OUTPUT_TEXT];
		if( !in_array( $format, $formats ) )
			throw new InvalidArgumentException( 'Invalid output format' );
		$this->output		= $format;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderAsHtml(): string
	{
		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( $this->words['panel-facts'] );
		$facts	= array_merge( $this->facts, [
			'price'		=> $this->helperShop->formatPrice( $this->order->priceTaxed ),
		] );
		foreach( $facts as $key => $value )
			$helperFacts->add( $key, $value );
		return $helperFacts->render();
	}

	protected function renderAsText(): string
	{
//		$words			= (object) $this->words['panel-facts'];
		$helperText		= new View_Helper_Mail_Text();
		$list			= [];
		$list[]			= $helperText->line( "=", 78 );
		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( $this->words['panel-facts'] );
		$helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT );
		$facts	= array_merge( $this->facts, [
			'price'		=> $this->helperShop->formatPrice( $this->order->priceTaxed, TRUE, FALSE ),
		] );
		foreach( $facts as $key => $value )
			$helperFacts->add( $key, $value );

		$list[]	= $helperFacts->render();
		$list[]	= $helperText->line( "-", 78 );
		return join( PHP_EOL, $list );
	}
}
