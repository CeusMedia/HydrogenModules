<?php
class Controller_Manage_Shop_Shipping extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->modelGrade	= new Model_Shop_Shipping_Grade( $this->env );
		$this->modelZone	= new Model_Shop_Shipping_Zone( $this->env );
		$this->modelPrice	= new Model_Shop_Shipping_Price( $this->env );
	}

	public function index(){
		$this->addData( 'zones', $this->modelZone->getAll( array(), array( 'title' => 'ASC' ) ) );
		$this->addData( 'grades', $this->modelGrade->getAll( array(), array( 'title' => 'ASC' ) ) );
		$this->addData( 'prices', $this->modelPrice->getAll( array(), array( 'zoneId' => 'ASC', 'gradeId' => 'ASC' ) ) );
	}

	public function addGrade(){
		$this->modelGrade->add( $this->request->getAll() );
		$this->restart( NULL, TRUE );
	}

	public function addZone(){
		$this->modelGrade->add( $this->request->getAll() );
		$this->restart( NULL, TRUE );
	}

	public function setPrices(){
		$grades	= $this->modelGrade->getAll();
		$zones	= $this->modelZone->getAll();
		foreach( $grades as $grade ){
			foreach( $zones as $zone ){
				$indices	= array(
					'shippingGradeId'	=> $grade->gradeId,
					'shippingZoneId'	=> $zone->zoneId,
				);
				$price	= $this->request->get( 'price' );
				$setPrice	= $this->modelPrice->getByIndices( $indices );
				if( !$setPrice )
					$this->modelPrice->add( array_merge( $indices ), array(
						'price'	=> $price,
					) );
				if( $setPrice && $setPrice->price != $price ){
					$this->modelPrice->editByIndices( $indices, array(
						'price'	=> $price,
					) );
				}
			}
		}
		( $this->request->getAll() );
		$this->restart( NULL, TRUE );
	}

	public function removeGrade( $gradeId ){
		$this->modelPrice->removeByIndex( 'gradeId', $gradeId );
		$this->modelGrade->remove( $gradeId );
		$this->restart( NULL, TRUE );
	}

	public function removeZone( $zoneId ){
		$this->modelPrice->removeByIndex( 'zoneId', $zoneId );
		$this->modelZone->remove( $zoneId );
		$this->restart( NULL, TRUE );
	}
}
?>
