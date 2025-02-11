<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Branch extends Model
{
	public const STATUS_INACTIVE	= -2;
	public const STATUS_REJECTED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_CHANGED		= 1;
	public const STATUS_ACTIVE		= 2;

	protected int $radiusEarth  	= 6371;

	protected string $name			= 'branches';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'branchId';

	protected array $indices		= [
		'companyId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	public function extendWithGeocodes( $branchId )
	{
		$branch	= $this->get( $branchId );
		try{
			$geocoder	= new Logic_Geocoder( $this->env );
			$tags	= $geocoder->geocodeAddress( $branch->street, $branch->number, $branch->postcode, $branch->city, 'Deutschland' );
			$coords	= $geocoder->convertRadianToCoords( $tags->longitude, $tags->latitude );
			$data	= [
				'longitude'	=> $tags->longitude,
				'latitude'	=> $tags->latitude,
				'x'			=> $coords->x,
				'y'			=> $coords->y,
				'z'			=> $coords->z,
			];
			$this->edit( $branchId, $data );
			return TRUE;
		}
		catch( Exception $e ){
die( $e->getMessage() );
			return FALSE;
		}
	}

	public function getAllInDistance( $x, $y, $z, $distance, array $havingIds = [] ): array
	{
		$query		= 'SELECT *
		FROM branches as b
		WHERE
			  POWER(' . $x .' - b.x, 2)
			+ POWER(' . $y .' - b.y, 2)
			+ POWER(' . $z .' - b.z, 2)
		<= ' . pow( $distance, 2 );
		if( $havingIds )
			$query	.= " AND branchId IN(".join( ", ", $havingIds ).")";
//xmp( $query );die;
		$list	= [];
		foreach( $this->env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $branch ){
			$powX	= pow( $x - $branch->x, 2);
			$powY	= pow( $y - $branch->y, 2);
			$powZ	= pow( $z - $branch->z, 2);
			$branch->distance	= sqrt( $powX + $powY + $powZ );
			$list[]	= $branch;
		}
		return $list;
	}

	/**
	 *	@todo		move to branch module and remove
	 */
	public function getBranchesInRangeOf( $point, $radius, array $havingIds = [] ): array
	{
		$list		= [];
		$model		= new Model_Branch( $this->env );
		$distance	= 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
		return $model->getAllInDistance( $point->x, $point->y, $point->z, $distance );
	}
}
