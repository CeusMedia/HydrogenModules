<?php
class Model_Address extends CMF_Hydrogen_Model{

	const STATUS_INACTIVE	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;

	protected $radiusEarth  = 6371;

	protected $name			= 'addresses';
	protected $columns		= array(
		'addressId',
		'status',
		'title',
		'country',
		'postcode',
		'city',
		'street',
		'number',
		'latitude',
		'longitude',
		'x',
		'y',
		'z',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'addressId';
	protected $indices		= array(
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function extendWithGeocodes( $addressId ){
		$address	= $this->get( $addressId );
		try{
			$geocoder	= new Logic_Geocoder( $this->env );
			$tags		= $geocoder->geocodeAddress(
				$address->street,
				$address->number,
				$address->postcode,
				$address->city,
				$address->country
			);
			$coords	= $geocoder->convertRadianToCoords( $tags->longitude, $tags->latitude );
			$data	= array(
				'longitude'	=> $tags->longitude,
				'latitude'	=> $tags->latitude,
				'x'			=> $coords->x,
				'y'			=> $coords->y,
				'z'			=> $coords->z,
			);
			$this->edit( $addressId, $data );
			return TRUE;
		}
		catch( Exception $e ){
die( $e->getMessage() );
			return FALSE;
		}
	}

	public function getAllInDistance( $x, $y, $z, $distance, $havingIds = array() ){
		$query		= 'SELECT *
		FROM addresses as a
		WHERE
			  POWER(' . $x .' - a.x, 2)
			+ POWER(' . $y .' - a.y, 2)
			+ POWER(' . $z .' - a.z, 2)
		<= ' . pow( $distance, 2 );
		if( $havingIds )
			$query	.= " AND addressId IN(".join( ", ", $havingIds ).")";
		$list	= array();
		foreach( $this->env->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $address ){
			$powX	= pow( $x - $address->x, 2);
			$powY	= pow( $y - $address->y, 2);
			$powZ	= pow( $z - $address->z, 2);
			$address->distance	= sqrt( $powX + $powY + $powZ );
			$list[]	= $branch;
		}
		return $list;
	}

	/**
	 *	@todo		move to branch module and remove
	 */
	public function getBranchesInRangeOf( $point, $radius, $havingIds = array() ){
		$list		= array();
		$model		= new Model_Branch( $this->env );
		$distance	= 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
		return $model->getAllInDistance( $point->x, $point->y, $point->z, $distance, $havingIds );
	}/**/
}
?>
