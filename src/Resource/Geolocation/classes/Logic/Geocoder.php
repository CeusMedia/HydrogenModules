<?php

use CeusMedia\Common\Net\API\Google\Maps\Geocoder as GoogleMapsGeocoder;

/**
 *	@todo		code doc
 *	@todo		allow different cache backends
 */
class Logic_Geocoder{

	protected \CeusMedia\HydrogenFramework\Environment $env;
	protected int $radiusEarth  = 6371;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function calculateDistance( int|float $radius ): float
	{
		return 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
	}

	public function convertRadianToCoords( float|string $longitude, float|string $latitude ): object
	{
		$lambda	= $longitude * pi() / 180;
		$phi	= $latitude * pi() / 180;
		$x		= $this->radiusEarth * cos( $phi ) * cos( $lambda );
		$y		= $this->radiusEarth * cos( $phi ) * sin( $lambda );
		$z		= $this->radiusEarth * sin( $phi );
		return (object) ['x' => $x, 'y' => $y, 'z' => $z];
	}

	public function geocodeAddress( string $street, string $number, string $postcode, string $city, string $country ): object
	{
		$geocoder	= new GoogleMapsGeocoder( "" );
		$geocoder->setCachePath( 'cache/geo/' );
		$query		= $street.' '.$number.', '.$postcode.' '.$city.', '.$country;
#		remark( $query );
#		$addr		= $geocoder->getAddress( utf8_encode( $query ) );
#		print_m( $addr );
		return (object) $geocoder->getGeoTags( $query );
	}

	public function getCities( string $startsWith ): array
	{
		$dbGeo	= new Model_OpenGeo( $this->env->getDatabase() );
		return $dbGeo->getCities( $startsWith );
	}

	public function getPointByCity( string $city ): ?object
	{
		$dbGeo	= new Model_OpenGeo( $this->env->getDatabase() );
		$point	= $dbGeo->geocodeCity( $city );
		if( NULL !== $point ){
			$coords		= $this->convertRadianToCoords( $point->lon, $point->lat );
			$point->x	= $coords->x;
			$point->y	= $coords->y;
			$point->z	= $coords->z;
		}
		return $point;
	}

	public function getPointByPostcodeAndCity( string $postcode, string $city ): object
	{
		$model		= new Model_OpenGeo_Postcode( $this->env );
		$indices	= ['postcode' => $postcode, 'city' => $city];
		if( !( $entry = $model->getByIndices( $indices ) ) )
			throw new OutOfRangeException( 'Invalid postcode ('.$postcode.') or city ('.$city.')' );
		$coords		= $this->convertRadianToCoords( $entry->longitude, $entry->latitude );
		$entry->x	= $coords->x;
		$entry->y	= $coords->y;
		$entry->z	= $coords->z;
		$entry->lon	= $entry->longitude;
		$entry->lat	= $entry->latitude;
		return $entry;
	}

	public function getPointByPostcode( string $postcode ): object
	{
		$dbGeo	= new Model_OpenGeo( $this->env->getDatabase() );
		$point	= $dbGeo->geocodePostcode_old( $postcode );
//print_m( $point );die;
		if( !$point )
			throw new OutOfRangeException( 'Postcode invalid or unknown' );
		$coords		= $this->convertRadianToCoords( $point->lon, $point->lat );
		$point->x	= $coords->x;
		$point->y	= $coords->y;
		$point->z	= $coords->z;
		return $point;
	}
}
