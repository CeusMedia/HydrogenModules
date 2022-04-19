<?php
class Logic_Shop_Shipping extends CMF_Hydrogen_Logic
{
	/**	@var		Model_Shop_Shipping_Country			$modelShippingCountry */
	protected $modelShippingCountry;

	/**	@var		Model_Shop_Shipping_Grade			$modelShippingGrade */
	protected $modelShippingGrade;

	/**	@var		Model_Shop_Shipping_Option			$modelShippingOption */
	protected $modelShippingOption;

	/**	@var		Model_Shop_Shipping_Price			$modelShippingPrice */
	protected $modelShippingPrice;

	/**	@var		Model_Shop_Shipping_Zone			$modelShippingZone */
	protected $modelShippingZone;

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		int		 $quantity		Quantity to ge Shipping Grade for
	 *	@return		int
	 *	@todo		remove method and its calls
	 */
	public function getGradeID( $quantity )
	{
		$model	= new Model_Shop_Shipping_Grade( $this->env );
		return array_shift( $model->getAll( $conditions, array ('quantity' => 'DESC' ) ) );
	}

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int		$shippingzone_id 		ID of Shipping Zone
	 *	@param		int		$shippinggrade_id 		ID of Shipping Grade
	 *	@return		string
	 *	@todo		remove method and its calls
	 */
	public function getPrice( $shippingzone_id, $shippinggrade_id )
	{
		$model		= new Model_Shop_Shipping_Price();
		$conditions	= array( 'shippingzone_id' => $shippingZoneId, 'shippinggrade_id' => $shippingGradeId );
		$data		= $model->getByIndices( $conditions );
		if( $data )
			return $data->price;
		return NULL;
	}

	/**
	 *	Alias for getShippingZoneId.
	 *	@param		integer		$country_id
	 *	@return		integer|NULL
	 *	@todo		remove method and its calls
	 */
	public function getZoneId( $country_id )
	{
		$model	= new Model_Shop_Shipping_Country();
		$data	= $model->getByIndex( 'country_id', $countryId );
		if( $data )
			return $data->shippingzone_id;
		return NULL;
	}

	protected function __onInit()
	{
		$this->modelShippingCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelShippingGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelShippingOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelShippingPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelShippingZone	= new Model_Shop_Shipping_Zone( $this->env );
	}
}
