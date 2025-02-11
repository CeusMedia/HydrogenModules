<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Event_Address extends Model
{
	public const STATUS_INACTIVE	= -2;
	public const STATUS_REJECTED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_CHANGED		= 1;
	public const STATUS_ACTIVE		= 2;

	public const STATUSES			= [
		self::STATUS_INACTIVE,
		self::STATUS_REJECTED,
		self::STATUS_NEW,
		self::STATUS_CHANGED,
		self::STATUS_ACTIVE,
	];

	protected int $radiusEarth		= 6371;

	protected string $name			= 'event_addresses';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'addressId';

	protected array $indices		= [
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	@param		int|string		$addressId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function extendWithGeocodes( int|string $addressId ): bool
	{
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
			$data	= [
				'longitude'	=> $tags->longitude,
				'latitude'	=> $tags->latitude,
				'x'			=> $coords->x,
				'y'			=> $coords->y,
				'z'			=> $coords->z,
			];
			$this->edit( $addressId, $data );
			return TRUE;
		}
		catch( Exception $e ){
			die( $e->getMessage() );
		}
		return FALSE;
	}

	/**
	 *	@param		float			$x
	 *	@param		float			$y
	 *	@param		int|float		$z
	 *	@param		int|float		$distance
	 *	@param array $havingIds
	 *	@return array
	 */
	public function getAllInDistance( float $x, float $y, int|float $z, int|float $distance, array $havingIds = [] ): array
	{
		$query		= 'SELECT *
		FROM addresses as a
		WHERE
			  POWER(' . $x .' - a.x, 2)
			+ POWER(' . $y .' - a.y, 2)
			+ POWER(' . $z .' - a.z, 2)
		<= ' . pow( $distance, 2 );
		if( $havingIds )
			$query	.= " AND addressId IN(".join( ", ", $havingIds ).")";
		$list	= [];
		foreach( $this->env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $address ){
			$powX	= pow( $x - $address->x, 2);
			$powY	= pow( $y - $address->y, 2);
			$powZ	= pow( $z - $address->z, 2);
			$address->distance	= sqrt( $powX + $powY + $powZ );
			$list[]	= $address;
		}
		return $list;
	}

	/**
	 *	@todo		move to branch module and remove
	 */
	public function getBranchesInRangeOf( object $point, $radius, array $havingIds = [] ): array
	{
		$model		= new Model_Branch( $this->env );
		$distance	= 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
		return $model->getAllInDistance( $point->x, $point->y, $point->z, $distance );
	}
}
