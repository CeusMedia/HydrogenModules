<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\NotSupported as NotSupportedException;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_CartPositions
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
	public const OUTPUT_HTML_LIST		= 3;

	public const OUTPUTS				= [
		self::OUTPUT_UNKNOWN,
		self::OUTPUT_TEXT,
		self::OUTPUT_HTML,
		self::OUTPUT_HTML_LIST,
	];

	protected Environment $env;

	protected Logic_ShopBridge $bridge;

	protected Dictionary $config;

	protected ?Model_Shop_Payment_BackendRegister $paymentsBackends	= NULL;

	protected ?Entity_Shop_Payment_Backend $paymentBackend			= NULL;

	protected ?Entity_Address $deliveryAddress						= NULL;

	/** @var Entity_Shop_Order_Position[] $positions */
	protected array $positions										= [];

	protected bool $changeable										= TRUE;

	protected int $display											= self::DISPLAY_BROWSER;

	protected int $output											= self::OUTPUT_HTML;

	protected array $words;

	protected ?string $forwardPath									= NULL;

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

	/**
	 *	Return rendered card as HTML or text, depending on set output format.
	 *	Returns empty string if cart is empty.
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		if( [] === $this->positions )
			return '';

		$strategy	= match( $this->output ){
			self::OUTPUT_HTML_LIST	=> new View_Helper_Shop_CartPositions_OutputStrategy_HtmlList( $this->env ),
			self::OUTPUT_HTML		=> new View_Helper_Shop_CartPositions_OutputStrategy_HTML( $this->env ),
			self::OUTPUT_TEXT		=> new View_Helper_Shop_CartPositions_OutputStrategy_Text( $this->env ),
		};
		switch( $this->output ){
			case self::OUTPUT_HTML:
			case self::OUTPUT_HTML_LIST:
				if( NULL !== $this->forwardPath )
					$strategy->setForwardPath( $this->forwardPath );
				$strategy->setChangeable( $this->changeable );
				break;
		}
		$strategy->setPositions( $this->positions );
		$strategy->setDisplay( $this->output );
		if( NULL !== $this->paymentBackend )
			$strategy->setPaymentBackend( $this->paymentBackend );
		if( NULL !== $this->paymentsBackends )
			$strategy->setPaymentBackends( $this->paymentsBackends );
		if( NULL !== $this->deliveryAddress )
			$strategy->setDeliveryAddress( $this->deliveryAddress );
		return $strategy->render();
	}

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
	 *	Set output format for rendering.
	 *	@param		int		$format
	 *	@return		self
	 */
	public function setOutput( int $format ): self
	{
		$formats	= array_diff( self::OUTPUTS, [self::OUTPUT_UNKNOWN] );
		if( !in_array( $format, $formats, TRUE ) )
			throw new InvalidArgumentException( 'Invalid output format' );
		$this->output	= $format;
		return $this;
	}

	/**
	 *	Sets payment backend by object or key string.
	 *	If given by key string, payment backends have to be set before.
	 *	@param		Entity_Shop_Payment_Backend|string	$backend
	 *	@return		self
	 *	@throws		RuntimeException		if backend is given by string and no payment backends have been set
	 *	@throws		NotSupportedException	if backend is given by string and no payment backend can be found by this key
	 */
	public function setPaymentBackend( Entity_Shop_Payment_Backend|string $backend ): self
	{
		if( is_string( $backend ) ){
			if( NULL === $this->paymentsBackends )
				throw new RuntimeException( 'No payment backends set, yet' );
			if( !$this->paymentsBackends->has( $backend ) )
				throw NotSupportedException::create()
					->setMessage( sprintf( 'No payment backend found by key "%s"', $backend ) );
			return $this->setPaymentBackend( $this->paymentsBackends->get( $backend ) );
		}
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
		return $this;
	}
}
