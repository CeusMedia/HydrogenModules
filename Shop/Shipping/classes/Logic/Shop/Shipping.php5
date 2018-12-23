<?php
class Logic_Shop_Shipping extends CMF_Hydrogen_Logic{

	/**	@var		Model_Shop_Shipping_Country			$modelCountry */
	protected $modelCountry;

	/**	@var		Model_Shop_Shipping_Grade			$modelGrade */
	protected $modelGrade;

	/**	@var		Model_Shop_Shipping_Option			$modelOption */
	protected $modelOption;

	/**	@var		Model_Shop_Shipping_Price			$modelPrice */
	protected $modelPrice;

	/**	@var		Model_Shop_Shipping_Zone			$modelZone */
	protected $modelZone;

	protected function __onInit(){
		$this->modelCountry	= new Model_Shop_Shipping_Country( $this->env );
		$this->modelGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelOption	= new Model_Shop_Shipping_Option( $this->env );
		$this->modelPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelZone	= new Model_Shop_Shipping_Zone( $this->env );
	}

	/**
	 *	Returns Shipping Grade ID by Quantity.
	 *	@access		public
	 *	@param		int		 $quantity		Quantity to get Shipping Grade for
	 *	@return		int
	 *	@todo		remove method and its calls
	 */
/*	public function getGradeId( $quantity ){
		$conditions	= array( 'zoneId' => $zoneId, 'gradeId' => $gradeId );
		return array_shift( $this->modelGrade->getAll( $conditions, array ('quantity' => 'DESC' ) ) );
	}*/

	/**
	 *	Returns Price of Shipping Grade in Shipping Zone.
	 *	@access		public
	 *	@param		int		$zoneId 		ID of Shipping Zone
	 *	@param		int		$gradeId 		ID of Shipping Grade
	 *	@return		string
	 *	@todo		remove method and its calls
	 */
	public function getPrice( $zoneId, $gradeId ){
		$indices	= array( 'zoneId' => $zoneId, 'gradeId' => $gradeId );
		$data		= $this->modelPrice->getByIndices( $indices );
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
		$data	= $this->modelZone->getByIndex( 'countryId', $countryId );
		if( $data )
			return $data->shippingzoneId;
		return NULL;
	}
}
?>
