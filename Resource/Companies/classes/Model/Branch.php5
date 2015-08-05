<?php
class Model_Branch extends CMF_Hydrogen_Model{

	const STATUS_INACTIVE	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;

	protected $name			= 'branches';
	protected $columns		= array(
		'branchId',
		'companyId',
		'status',
		'title',
		'description',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'url',
		'email',
		'longitude',
		'latitude',
		'x',
		'y',
		'z',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'branchId';
	protected $indices		= array(
		'companyId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	public function extendWithGeocodes( $branchId ){
		$branch	= $this->get( $branchId );
		try{
			$geocoder	= new Logic_Geocoder( $this->env );
			$tags	= $geocoder->geocodeAddress( $branch->street, $branch->number, $branch->postcode, $branch->city, 'Deutschland' );
			$coords	= $geocoder->convertRadianToCoords( $tags->longitude, $tags->latitude );
			$data	= array(
				'longitude'	=> $tags->longitude,
				'latitude'	=> $tags->latitude,
				'x'			=> $coords->x,
				'y'			=> $coords->y,
				'z'			=> $coords->z,
			);
			$this->edit( $branchId, $data );
			return TRUE;
		}
		catch( Exception $e ){
die( $e->getMessage() );
			return FALSE;
		}
	}

	public function getAllInDistance( $x, $y, $z, $distance ){
		$query		= 'SELECT *
		FROM branches as b
		WHERE
			  POWER(' . $x .' - b.x, 2)
			+ POWER(' . $y .' - b.y, 2)
			+ POWER(' . $z .' - b.z, 2)
		<= ' . pow( $distance, 2 );
		return $this->env->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
	}
}
?>
