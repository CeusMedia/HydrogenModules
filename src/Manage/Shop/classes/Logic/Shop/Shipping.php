<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Shop_Shipping extends Logic
{
	/**	@var		Model_Shop_Shipping_Country			$modelShippingCountry */
	protected Model_Shop_Shipping_Country $modelShippingCountry;

	/**	@var		Model_Shop_Shipping_Grade			$modelShippingGrade */
	protected Model_Shop_Shipping_Grade $modelShippingGrade;

	/**	@var		Model_Shop_Shipping_Option			$modelShippingOption */
	protected Model_Shop_Shipping_Option $modelShippingOption;

	/**	@var		Model_Shop_Shipping_Price			$modelShippingPrice */
	protected Model_Shop_Shipping_Price $modelShippingPrice;

	/**	@var		Model_Shop_Shipping_Zone			$modelShippingZone */
	protected Model_Shop_Shipping_Zone $modelShippingZone;

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		int		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 *	@todo		remove method and its calls
	 *	@deprecated
	 */
	public function getGradeID( int $quantity )
	{
		$model	= new Model_Shop_Shipping_Grade( $this->env );
		$grades	= $model->getAll( [], ['quantity' => 'DESC'] );
		return array_shift( $grades );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int		$shippingZoneId 		ID of Shipping Zone
	 *	@param		int		$shippingGradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@todo		remove method and its calls
	 *	@deprecated
	 */
	public function getPrice( $shippingZoneId, $shippingGradeId )
	{
		$model		= new Model_Shop_Shipping_Price( $this->env );
		$conditions	= ['zoneId' => $shippingZoneId, 'gradeId' => $shippingGradeId];
		$data		= $model->getByIndices( $conditions );
		if( $data )
			return $data->price;
		return NULL;
	}

	/**
	 *	Alias for getShippingZoneId.
	 *	@param		integer		$countryId
	 *	@return		integer|NULL
	 *	@todo		remove method and its calls
	 */
	public function getZoneId( $countryId )
	{
		$model	= new Model_Shop_Shipping_Country( $this->env );
		$data	= $model->getByIndex( 'countryId', $countryId );
		if( $data )
			return $data->zoneId;
		return NULL;
	}

	protected function __onInit(): void
	{
		$this->modelShippingCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelShippingGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelShippingOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelShippingPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelShippingZone	= new Model_Shop_Shipping_Zone( $this->env );
	}
}
