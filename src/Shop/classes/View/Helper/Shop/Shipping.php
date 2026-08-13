<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_Shipping
{
	protected Environment $env;
	protected ?Entity_Address $deliveryAddress	= NULL;
	protected ?string $deliveryCountry	= NULL;
	protected ?Entity_Shop_Order $order = NULL;
	protected array $positions = [];
	protected ?float $totalWeight		= NULL;

	/**
	 *	@param		Environment			$env
	 */
	public function __construct( Environment $env )
	{
		$this->env = $env;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		try{
			$totalWeight		= $this->getTotalWeight();
			$deliveryCountry	= $this->getDeliveryCountry();
		}
		catch ( Throwable $e ){
			return '';
		}

		$logic			= Logic_Shop_Shipping::getInstance( $this->env );
		$modelZone		= new Model_Shop_Shipping_Zone( $this->env );
		$modelGrade		= new Model_Shop_Shipping_Grade( $this->env );
		$modelCountry	= new Model_Shop_Shipping_Country( $this->env );
		$modelPrice		= new Model_Shop_Shipping_Price( $this->env );

		$priceMap		= [];
		foreach( $modelPrice->getAll() as $price ){
			if( !array_key_exists( $price->zoneId, $priceMap ) )
				$priceMap[$price->zoneId]	= [];
			if( !array_key_exists( $price->gradeId, $priceMap[$price->zoneId] ) )
				$priceMap[$price->zoneId][$price->gradeId]	= [];
			$priceMap[$price->zoneId][$price->gradeId]	= $price->price;
		}

		$userZone	= $logic->getZoneFromCountryCode( $deliveryCountry );
		$userGrade	= $logic->getGradeFromWeight( $totalWeight );

		$grades		= $modelGrade->getAll();
		$list		= [];
		$row		= [];
		$row[]		= HtmlTag::create( 'th', '<big><strong>Versandkosten</strong></big>' );
		foreach( $grades as $grade )
			$row[]	= HtmlTag::create( 'th', $grade->title );
		$list[]		= HtmlTag::create( 'tr', $row );

		$countryLabels	= $this->env->getLanguage()->getWords( 'address' )['countries'];

		foreach( $modelZone->getAll() as $zone ){
			$isUserZone	= $userZone->zoneId == $zone->zoneId;
			$row		= [];
			$countries	= $modelCountry->getAllByIndex( 'zoneId', $zone->zoneId, [], [], ['countryCode'] );
			foreach( $countries as $nr => $country )
				$countries[$nr]	= $countryLabels[$country];

			$countryList	= count( $countries ) > 2 ? join( ', ', $countries ) : '';
			$row[]			= HtmlTag::create( 'th', $zone->title.'<br/><small class="muted">'.$countryList.'</small>' );
			foreach( $grades as $grade ){
				$price	= $this->formatPrice( $priceMap[$zone->zoneId][$grade->gradeId] );
				if( $isUserZone && $userGrade->gradeId == $grade->gradeId )
					$price	= HtmlTag::create( 'big', $price, ['class' => ''] );
				$row[]		= HtmlTag::create( 'td', $price );
			}
			$list[]		= HtmlTag::create( 'tr', $row, ['class' => $isUserZone ? 'success' : '' ] );
		}

		return HtmlTag::create( 'table', [
			HtmlTag::create( 'tbody', $list ),
		], ['class' => 'table table-fixed table-bordered'] );
	}

	/**
	 *	@param		Entity_Address		$deliveryAddress
	 *	@return		self
	 */
	public function setDeliveryAddress( Entity_Address $deliveryAddress ): self
	{
		$this->deliveryAddress	= $deliveryAddress;
		return $this;
	}

	/**
	 *	@param		string		$deliveryCountry
	 *	@return		self
	 */
	public function setDeliveryCountry( string $deliveryCountry ): self
	{
		$this->deliveryCountry	= $deliveryCountry;
		return $this;
	}

	/**
	 *	@param		Entity_Shop_Order $order
	 *	@return		self
	 */
	public function setOrder( Entity_Shop_Order $order ): self
	{
		$this->order	= $order;
		return $this;
	}

	/**
	 *	@param		array		$positions
	 *	@return		self
	 */
	public function setPositions( array $positions ): self
	{
		$this->positions	= $positions;
		return $this;
	}

	/**
	 *	@param		float		$totalWeight
	 *	@return		self
	 */
	public function setTotalWeight( float $totalWeight ): self
	{
		$this->totalWeight	= $totalWeight;
		return $this;
	}


	//  --  PROTECTED  --  //


	/**
	 *	@param		float		$price
	 *	@param		bool		$spaceBeforeCurrency
	 *	@param		bool		$asHtml
	 *	@return		string
	 */
	protected function formatPrice( float $price, bool $spaceBeforeCurrency = TRUE, bool $asHtml = TRUE ): string
	{
		$helper		= new View_Helper_Shop( $this->env );
		return $helper->formatPrice( $price, $spaceBeforeCurrency, $asHtml );
	}

	/**
	 *	@return		float
	 *	@throws		ReflectionException
	 */
	protected function getTotalWeight(): float
	{
		if( NULL !== $this->totalWeight )
			return $this->totalWeight;
		$totalWeight	= 0;
		if( [] !== $this->positions ){
			foreach( $this->positions as $position )
				$totalWeight	+= $position->article->weight->all;
			return $totalWeight;
		}
		if( !NULL !== $this->order ){
			$logicShop			= Logic_Shop::getInstance( $this->env );
			$this->positions	= $logicShop->getOrderPositions( $this->order->orderId );
			return $this->getTotalWeight();
		}
		throw new RuntimeException( 'None of these set: totalWeight, positions, order' );
	}

	/**
	 *	@return		string|NULL
	 *	@throws		ReflectionException
	 */
	protected function getDeliveryCountry(): ?string
	{
		if( NULL !== $this->deliveryCountry )
			return $this->deliveryCountry;
		if( NULL !== $this->deliveryAddress )
			return $this->deliveryAddress->country;

		$logicShop		= Logic_Shop::getInstance( $this->env );
		if( NULL !== $this->order ){
			if( NULL !== $this->order->customer ){
				if( NULL !== $this->order->customer->addressDelivery )
					return $this->order->customer->addressDelivery->country;
				$address	= $logicShop->getDeliveryAddressFromCart();
				if( NULL !== $address )
					return $address->country;
			}
			return NULL;
		}
		throw new RuntimeException( 'None of these set: deliveryCountry, deliveryAddress, order' );
	}
}