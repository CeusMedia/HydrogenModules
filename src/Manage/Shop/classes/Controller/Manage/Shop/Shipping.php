<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Shop_Shipping extends Controller
{
	/** @var		Model_Shop_Shipping_Grade		$modelGrade */
	protected $modelGrade;

	/** @var		Model_Shop_Shipping_Zone		$modelZone */
	protected $modelZone;

	/** @var		Model_Shop_Shipping_Price		$modelPrice */
	protected $modelPrice;

	/** @var		Model_Shop_Shipping_Country		$modelCountry */
	protected $modelCountry;

	public function index()
	{
		$countryMap		= $this->getWords( 'countries', 'address' );
		$grades			= $this->modelGrade->getAll( [], ['fallback' => 'ASC', 'weight' => 'ASC'] );
		$prices			= $this->modelPrice->getAll( [], ['zoneId' => 'ASC', 'gradeId' => 'ASC'] );

		$priceMatrix	= [];
		foreach( $prices as $price ){
			if( !isset( $priceMatrix[$price->zoneId] ) )
				$priceMatrix[$price->zoneId]	= [];
			$priceMatrix[$price->zoneId][$price->gradeId]	= $price->price;
		}

		$zones			= $this->modelZone->getAll( [], ['fallback' => 'ASC', 'zoneId' => 'ASC'] );
		foreach( $zones as $zone )
			$zone->countries	= $this->modelCountry->getAllByIndex( 'zoneId', $zone->zoneId, [], [], ['countryCode'] );

		$zoneCountries	= $this->modelCountry->getAll( [], [], [], ['countryCode'] );

		$this->addData( 'zones', $zones );
		$this->addData( 'grades', $grades );
		$this->addData( 'prices', $prices );
		$this->addData( 'priceMatrix', $priceMatrix );
		$this->addData( 'zoneCountries', $zoneCountries );
		$this->addData( 'countryMap', $countryMap );
	}

	public function addGrade()
	{
		$data		= ['title' => $this->request->get( 'title' )];
		if( $this->request->get( 'fallback' ) )
			$data['fallback']	= 1;
		else
			$data['weight']		= $this->request->get( 'weight' );
		$gradeId	= $this->modelGrade->add( $data );

		foreach( $this->request->get( 'price' ) as $zoneId => $price )
			$this->modelPrice->add( [
				'gradeId'	=> $gradeId,
				'zoneId'	=> $zoneId,
				'price'		=> $price,
			] );

		$this->restart( NULL, TRUE );
	}

	public function addZone()
	{
		$zoneId	= $this->modelZone->add( array(
			'title'	=> $this->request->get( 'title' ),
		) );
		if( $this->request->get( 'fallback' ) )
			$this->modelZone->edit( $zoneId, ['fallback' => 1] );
		else{
			foreach( $this->request->get( 'country' ) as $countryCode )
			$this->modelCountry->add( [
				'zoneId'		=> $zoneId,
				'countryCode'	=> $countryCode,
			] );
		}

		foreach( $this->request->get( 'price' ) as $gradeId => $price )
			$this->modelPrice->add( [
				'gradeId'	=> $gradeId,
				'zoneId'	=> $zoneId,
				'price'		=> $price,
			] );

		$this->restart( NULL, TRUE );
	}

	public function setPrices()
	{
		$grades	= $this->modelGrade->getAll();
		$zones	= $this->modelZone->getAll();
		$prices	= $this->request->get( 'price' );

		$priceMatrix	= [];
		foreach( $this->modelPrice->getAll() as $price ){
			if( !isset( $priceMatrix[$price->zoneId] ) )
				$priceMatrix[$price->zoneId]	= [];
			$priceMatrix[$price->zoneId][$price->gradeId]	= $price->price;
		}

		foreach( $grades as $grade ){
			foreach( $zones as $zone ){
				$indices	= [
					'gradeId'	=> $grade->gradeId,
					'zoneId'	=> $zone->zoneId,
				];
				if( isset( $prices[$zone->zoneId][$grade->gradeId] ) ){
					$price	= $prices[$zone->zoneId][$grade->gradeId];
					$price	= str_replace( ',', '.', $price );
					if( !isset( $priceMatrix[$zone->zoneId][$grade->gradeId] ) ){
						$this->modelPrice->add( array_merge( $indices ), [
							'price'	=> $price,
						] );
					}
					else{
						if( $priceMatrix[$zone->zoneId][$grade->gradeId] != $price ){
							$this->modelPrice->editByIndices( $indices, [
								'price'	=> $price,
							] );
						}
					}
				}
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function removeGrade( $gradeId )
	{
		$this->modelPrice->removeByIndex( 'gradeId', $gradeId );
		$this->modelGrade->remove( $gradeId );
		$this->restart( NULL, TRUE );
	}

	public function removeZone( $zoneId )
	{
		$this->modelPrice->removeByIndex( 'zoneId', $zoneId );
		$this->modelCountry->removeByIndex( 'zoneId', $zoneId );
		$this->modelZone->remove( $zoneId );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->modelGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelZone	= new Model_Shop_Shipping_Zone( $this->env );
		$this->modelPrice	= new Model_Shop_Shipping_Price( $this->env );
		$this->modelCountry	= new Model_Shop_Shipping_Country( $this->env );
	}
}
