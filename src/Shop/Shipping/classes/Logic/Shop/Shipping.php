<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Shop_Shipping extends Logic
{
	/**	@var		Model_Shop_Shipping_Country			$modelCountry */
	protected Model_Shop_Shipping_Country $modelCountry;

	/**	@var		Model_Shop_Shipping_Grade			$modelGrade */
	protected Model_Shop_Shipping_Grade $modelGrade;

	/**	@var		Model_Shop_Shipping_Option			$modelOption */
	protected Model_Shop_Shipping_Option $modelOption;

	/**	@var		Model_Shop_Shipping_Price			$modelPrice */
	protected Model_Shop_Shipping_Price $modelPrice;

	/**	@var		Model_Shop_Shipping_Zone			$modelZone */
	protected Model_Shop_Shipping_Zone $modelZone;

	/**
	 *	Returns shipping price by country code and total weight of cart content.
	 *	@access		public
	 *	@param		string		$countryCode		Country code, like DE or AT
	 *	@param		integer		$weight				Total weight of cart content
	 *	@return		float
	 */
	public function getPriceFromCountryCodeAndWeight( string $countryCode, int $weight ): float
	{
		$zone	= $this->getZoneFromCountryCode( $countryCode );
		$grade	= $this->getGradeFromWeight( $weight );
		$price	= $this->modelPrice->getByIndices( [
			'zoneId'	=> $zone->zoneId,
			'gradeId'	=> $grade->gradeId,
		] );
		return (float) $price->price;
	}

	/**
	 *	Get shipping zone from country code.
	 *	If country code is not assigned to a zone, the fallback zone will be returned, if existing.
	 *	@access		public
	 *	@param		string		$countryCode		Country code, like DE or AT
	 *	@return 	object
	 *	@throws		RangeException if country code is neither assigned to a zone nor a fallback zone is existing.
	 */
	public function getZoneFromCountryCode( string $countryCode ): object
	{
		$country	= $this->modelCountry->getByIndex( 'countryCode', $countryCode );
		if( $country )
			return $this->modelZone->get( $country->zoneId );
		$zone	= $this->modelZone->getByIndex( 'fallback', 1 );
		if( $zone )
			return $zone;
		throw new RangeException( 'No zone found for country code: '.$countryCode );
	}

	/**
	 *	Get shipping grade from weight.
	 *	If weight is not covered by a grade, the fallback grade will be returned, if existing.
	 *	@access		public
	 *	@param		integer		$weight			Total weight of cart content in grams
	 *	@return 	object
	 *	@throws		RangeException if weight is neither covered by a zone nor a fallback grade is existing.
	 */
	public function getGradeFromWeight( int $weight ): object
	{
		$grades	= $this->modelGrade->getAll( ['fallback' => 0], ['weight' => 'ASC'] );
		foreach( $grades as $grade ){
			if( (int) $grade->weight > (int) $weight )
				return $grade;
		}
		$grade	= $this->modelGrade->getByIndex( 'fallback', 1 );
		if( $grade )
			return $grade;
		throw new RangeException( 'No grade found for weight: '.$weight );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int|string		$zoneId 		ID of Shipping Zone
	 *	@param		int|string		$gradeId 		ID of Shipping Grade
	 *	@return		string|NULL
	 *	@todo		remove method and its calls
	 *	@deprecated
	 */
	public function getPrice( int|string $zoneId, int|string $gradeId ): ?string
	{
		$indices	= ['zoneId' => $zoneId, 'gradeId' => $gradeId];
		$data		= $this->modelPrice->getByIndices( $indices );
		if( $data )
			return $data->price;
		return NULL;
	}

	/**
	 *	Alias for getShippingZoneId.
	 *	@param		int|string		$countryId
	 *	@return		string|NULL
	 *	@todo		remove method and its calls
	 *	@deprecated
	 */
	public function getZoneId( int|string $countryId ): ?string
	{
		$data	= $this->modelZone->getByIndex( 'countryId', $countryId );
		if( $data )
			return $data->shippingzoneId;
		return NULL;
	}

	protected function __onInit(): void
	{
		$this->modelCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelZone	= new Model_Shop_Shipping_Zone( $this->env );
	}
}
