<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;

abstract class View_Helper_Shop_Cart_Positions_OutputStrategy_Abstract
{
	public const DISPLAY_UNKNOWN		= 0;
	public const DISPLAY_BROWSER		= 1;
	public const DISPLAY_MAIL			= 2;

	public const DISPLAYS				= [
		self::DISPLAY_UNKNOWN,
		self::DISPLAY_BROWSER,
		self::DISPLAY_MAIL,
	];

	protected Logic_ShopBridge $bridge;

	protected Dictionary $config;
	protected Environment $env;
	protected ?Entity_Address $deliveryAddress						= NULL;
	protected ?Model_Shop_Payment_BackendRegister $paymentsBackends	= NULL;
	protected ?Entity_Shop_Payment_Backend $paymentBackend			= NULL;
	/** @var Entity_Shop_Order_Position[] $positions */
	protected array $positions										= [];

	protected int $display											= self::DISPLAY_BROWSER;
	protected ?string $forwardPath									= NULL;
	protected bool $changeable										= TRUE;
	protected array $words;

	/**
	 *	@param		Environment				$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.resource_shop.', TRUE );
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
		$this->bridge	= new Logic_ShopBridge( $this->env );
	}

	abstract public function render(): string;

	/**
	 *	@param		bool	$isChangeable
	 *	@return		self
	 */
	public function setChangeable( bool $isChangeable = TRUE ): self
	{
		$this->changeable	= $isChangeable;
		return $this;
	}

	/**
	 *	@param		Entity_Address		$address
	 *	@return		self
	 */
	public function setDeliveryAddress( Entity_Address $address ): self
	{
		$this->deliveryAddress	= $address;
		return $this;
	}

	/**
	 *	Set display type for rendering.
	 *	@param		int		$display
	 *	@return		self
	 */
	public function setDisplay( int $display ): self
	{
		$displays	= array_diff( self::DISPLAYS, [self::DISPLAY_UNKNOWN] );
		if( !in_array( $display, $displays, TRUE ) )
			throw new InvalidArgumentException( 'Invalid display format' );
		$this->display	= $display;
		return $this;
	}

	/**
	 *	@param		string		$forwardPath
	 *	@return		self
	 */
	public function setForwardPath( string $forwardPath ): self
	{
		$this->forwardPath	= $forwardPath;
		return $this;
	}

	/**
	 *	Sets payment backend by object or key string.
	 *	If given by key string, payment backends have to be set before.
	 *	@param		Entity_Shop_Payment_Backend		$backend
	 *	@return		self
	 *	@throws		RuntimeException		if backend is given by string and no payment backends have been set
	 */
	public function setPaymentBackend( Entity_Shop_Payment_Backend $backend ): self
	{
		$this->paymentBackend	= $backend;
		return $this;
	}

	/**
	 *	Set list of available payment methods by payment backend register.
	 *	@param		Model_Shop_Payment_BackendRegister	$backends
	 *	@return		self
	 */
	public function setPaymentBackends( Model_Shop_Payment_BackendRegister $backends ): self
	{
		$this->paymentsBackends	= $backends;
		return $this;
	}

	/**
	 *	Set list of positions.
	 *	@param		Entity_Shop_Order_Position[]	$positions
	 *	@return		self
	 */
	public function setPositions( array $positions ): self
	{
		$this->positions	= $positions;
		foreach( $positions as $position ){
			if( !isset( $position->article ) ){
				$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );
				$article	= $source->get( $position->articleId, $position->quantity );
				$position->article	= $article;
			}
		}
		return $this;
	}
}