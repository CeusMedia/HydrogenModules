<?php
class Logic_Shop_Shipping extends CMF_Hydrogen_Environment_Resource_Logic{

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

	protected function __onInit(){
/*		$this->modelShippingCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelShippingGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelShippingOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelShippingPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelShippingZone	= new Model_Shop_Shipping_Zone( $this->env );
*/	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		int		 $quantity		Quantity to get Shipping Grade for
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
	 *	@param		int		$zoneId 		ID of Shipping Zone
	 *	@param		int		$gradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@todo		remove method and its calls
	 */
	public function getPrice( $zoneId, $gradeId )
	{
		$model		= new Model_Shop_Shipping_Price();
		$conditions	= array( 'shippingzoneId' => $zoneId, 'shippinggradeId' => $gradeId );
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
	public function getZoneId( $countryId ){
		$model	= new Model_Shop_Shipping_Country();
		$data	= $model->getByIndex( 'countryId', $countryId );
		if( $data )
			return $data->shippingzoneId;
		return NULL;
	}
}
?>