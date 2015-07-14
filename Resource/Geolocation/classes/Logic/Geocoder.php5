<?php
/**
 *	@see		http://www.mamat-online.de/umkreissuche/opengeodb.php#code
 *	@see		http://opengeodb.giswiki.org/wiki/Datenbank_erstellen
 */
class Logic_OpenGeoDB{

	public function __construct( Database_PDO_Connection $dbc ){
		$this->dbc	= $dbc;
	}

	public function geocodeCity( $city ){
		$query	= 'SELECT zc_lat AS lat, zc_lon AS lon
			FROM geodb_zip_coordinates
			WHERE zc_location_name = ' . $this->dbc->quote( $city ).'
			ORDER BY zc_zip ASC';
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		return array_pop( $locations );
		return array_shift( $locations );
	}

	public function geocodePostcode( $postcode ){
		$query	= 'SELECT coo.lon, coo.lat
			FROM geodb_coordinates AS coo
			INNER JOIN geodb_textdata AS textdata ON textdata.loc_id = coo.loc_id
			WHERE textdata.text_val = ' . $this->dbc->quote( $postcode ) . '
			AND textdata.text_type = "500300000"';
		return $this->dbc->query( $query )->fetch( PDO::FETCH_OBJ );
	}

	public function geocodePostcode2( $postcode ){
		$query	= 'SELECT zc_id, zc_location_name, zc_lat, zc_lon
			FROM geodb_zip_coordinates
			WHERE zc_zip = ' . $this->dbc->quote( $postcode );
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( $locations ){
			$location	= array_shift( $locations );
			return (object) array(
				'lat'	=> $location->zc_lat,
				'lon'	=> $location->zc_lon,
			);
		}
		return NULL;
	}

	public function getCities( $startsWith, $limit = 10 ){
		$list	= array();
		if( !strlen( trim( $startsWith ) ) )
			return $list;
		$column	= 'city';
		if( preg_match( "/^[0-9]+$/", $startsWith ) )
			$column	= 'postcode';
		$query	= 'SELECT DISTINCT city, postcode, latitude, longitude
			FROM postcodes
			WHERE ' . $column . ' LIKE ' . $this->dbc->quote( $startsWith.'%' ) . '
			LIMIT 0, '.$limit;
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		foreach( $locations as $location ){
			$key	= $location->postcode.' '.strtolower( $location->city );
			if( empty( $list[$key] ) ){
				$list[$key]	= (object) array(
					'city'	=> $location->city,
					'zip'	=> $location->postcode,
					'lat'	=> $location->latitude,
					'lon'	=> $location->longitude,
				);
			}
		}
		ksort( $list );
		return array_values( $list );

		$list	= array();
		if( !strlen( trim( $startsWith ) ) )
			return $list;
		$column	= 'zc_location_name';
		if( preg_match( "/^[0-9]+$/", $startsWith ) )
			$column	= 'zc_zip';
		$query	= 'SELECT DISTINCT zc_location_name, zc_zip, zc_lat, zc_lon
			FROM geodb_zip_coordinates
			WHERE ' . $column . ' LIKE ' . $this->dbc->quote( $startsWith.'%' ) . '
			LIMIT 0, '.$limit;
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		foreach( $locations as $location ){
			$key	= strtolower( $location->zc_location_name );
			if( empty( $list[$key] ) ){
				$list[$key]	= (object) array(
					'city'	=> $location->zc_location_name,
					'zip'	=> $location->zc_zip,
					'lat'	=> $location->zc_lat,
					'lon'	=> $location->zc_lon,
				);
			}
		}
		ksort( $list );
		return array_values( $list );
	}
}
?>
<?php
class Logic_Geocoder{

	protected $radiusEarth	= 6371;

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
		$dbGeo	= new Logic_OpenGeoDB( $this->env->dbc );
		return $dbGeo->getCities( $startsWith );
	}

	public function getPointByCity( $city ){
		$dbGeo	= new Logic_OpenGeoDB( $this->env->dbc );
		$point	= $dbGeo->geocodeCity( $city );
		$coords		= $this->convertRadianToCoords( $point->lon, $point->lat );
		$point->x	= $coords->x;
		$point->y	= $coords->y;
		$point->z	= $coords->z;
		return $point;

	}

	public function getPointByPostcodeAndCity( $postcode, $city ){
		$model		= new Model_Postcode( $this->env );
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
		$dbGeo	= new Logic_OpenGeoDB( $this->env->dbc );
		$point	= $dbGeo->geocodePostcode2( $postcode );
//print_m( $point );die;
		if( !$point )
			throw new OutOfRangeException( 'Postcode invalid or unknown' );
		$coords		= $this->convertRadianToCoords( $point->lon, $point->lat );
		$point->x	= $coords->x;
		$point->y	= $coords->y;
		$point->z	= $coords->z;
		return $point;
	}

	public function getBranchesInRangeOf( $point, $radius, $havingIds = array() ){
		$list		= array();
		$model		= new Model_Branch( $this->env );
		$distance	= 2 * $this->radiusEarth * sin( $radius / ( 2 * $this->radiusEarth ) );
		return $model->getAllInDistance( $point->x, $point->y, $point->z, $distance, $havingIds );
	}
}
?>
