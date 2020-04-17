<?php
/**
 *	@todo		code doc
 *	@todo		allow different cache backends
 */
class Logic_Geocoder{

	protected $radiusEarth  = 6371;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function calculateDistance( $radius ){
		return 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
	}

	public function convertRadianToCoords( $longitude, $latitude ){
		$lambda	= $longitude * pi() / 180;
		$phi	= $latitude * pi() / 180;
		$x		= $this->radiusEarth * cos( $phi ) * cos( $lambda );
		$y		= $this->radiusEarth * cos( $phi ) * sin( $lambda );
		$z		= $this->radiusEarth * sin( $phi );
		return (object) array( 'x' => $x, 'y' => $y, 'z' => $z );
	}

	public function geocodeAddress( $street, $number, $postcode, $city, $country ){
		$geocoder	= new Net_API_Google_Maps_Geocoder( "" );
		$geocoder->setCachePath( 'cache/geo/' );
		$query		= $street.' '.$number.', '.$postcode.' '.$city.', '.$country;
#		remark( $query );
#		$addr		= $geocoder->getAddress( utf8_encode( $query ) );
#		print_m( $addr );
		return (object) $geocoder->getGeoTags( $query );
	}

	public function getCities( $startsWith ){
		$dbGeo	= new Model_OpenGeo( $this->env->getDatabase() );
		return $dbGeo->getCities( $startsWith );
	}

	public function getPointByCity( $city ){
		$dbGeo	= new Model_OpenGeo( $this->env->getDatabase() );
		$point	= $dbGeo->geocodeCity( $city );
		$coords		= $this->convertRadianToCoords( $point->lon, $point->lat );
		$point->x	= $coords->x;
		$point->y	= $coords->y;
		$point->z	= $coords->z;
		return $point;

	}

	public function getPointByPostcodeAndCity( $postcode, $city ){
		$model		= new Model_OpenGeo_Postcode( $this->env );
		$indices	= array( 'postcode' => $postcode, 'city' => $city );
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

	public function getPointByPostcode( $postcode ){
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
?>
