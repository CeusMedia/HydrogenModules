<?php
/**
 *	@see		http://www.mamat-online.de/umkreissuche/opengeodb.php#code
 *	@see		http://opengeodb.giswiki.org/wiki/Datenbank_erstellen
 */
class Model_OpenGeo{

	protected $dbc;

	public function __construct( $dbc ){
		$this->dbc	= $dbc;
	}

	public function geocodeCity( $city ){
		$query	= 'SELECT zc_lat AS lat, zc_lon AS lon
			FROM geodb_zip_coordinates
			WHERE zc_location_name = ' . $this->dbc->quote( $city ).'
			ORDER BY zc_zip ASC';
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( $locations ){
			return array_pop( $locations );
			return array_shift( $locations );
		}
		throw new InvalidArgumentException( 'City "'.$city.'" not found' );
	}

	public function geocodePostcode( $postcode ){
		$query	= 'SELECT coo.lon, coo.lat
			FROM geodb_coordinates AS coo
			INNER JOIN geodb_textdata AS textdata ON textdata.loc_id = coo.loc_id
			WHERE textdata.text_val = ' . $this->dbc->quote( $postcode ) . '
			AND textdata.text_type = "500300000"';
		$locations	= $this->dbc->query( $query )->fetch( PDO::FETCH_OBJ );
		if( $locations )
			return array_shift( $locations );
		throw new InvalidArgumentException( 'Postcode "'.$postcode.'" not found' );
		return NULL;
	}

	public function geocodePostcode_old( $postcode ){
		$query	= 'SELECT zc_lat AS lat, zc_lon AS lon
			FROM geodb_zip_coordinates
			WHERE zc_zip = ' . $this->dbc->quote( $postcode );
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( $locations )
			return array_shift( $locations );
		throw new InvalidArgumentException( 'Postcode "'.$postcode.'" not found' );
		return NULL;
	}

	public function getCities( $startsWith, $limit = 10, $offset = 0 ){
		$list	= [];
		if( !strlen( trim( $startsWith ) ) )
			return $list;
		$column	= 'city';
		if( preg_match( "/^[0-9]+$/", $startsWith ) )
			$column	= 'postcode';
		$query	= 'SELECT DISTINCT city, postcode, latitude, longitude
			FROM postcodes
			WHERE ' . $column . ' LIKE ' . $this->dbc->quote( $startsWith.'%' ) . '
			LIMIT '.$offset.', '.$limit;
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		foreach( $locations as $location ){
			$key	= $location->postcode.' '.strtolower( $location->city );
			if( empty( $list[$key] ) ){
				$list[$key]	= (object) [
					'city'	=> $location->city,
					'zip'	=> $location->postcode,
					'lat'	=> $location->latitude,
					'lon'	=> $location->longitude,
				];
			}
		}
		ksort( $list );
		return array_values( $list );
	}

	public function getCities_old( $startsWith, $limit = 10, $offset = 0 ){
		$list	= [];
		if( !strlen( trim( $startsWith ) ) )
			return $list;
		$column	= 'zc_location_name';
		if( preg_match( "/^[0-9]+$/", $startsWith ) )
			$column	= 'zc_zip';
		$query	= 'SELECT DISTINCT zc_location_name AS city, zc_zip AS zip, zc_lat AS lat, zc_lon AS lon
			FROM geodb_zip_coordinates
			WHERE ' . $column . ' LIKE ' . $this->dbc->quote( $startsWith.'%' ) . '
			LIMIT 0, '.$limit;
		$locations	= $this->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		foreach( $locations as $location ){
			$key	= strtolower( $location->city);
			if( empty( $list[$key] ) ){
				$list[$key]	= (object) [
					'city'	=> $location->city,
					'zip'	=> $location->zip,
					'lat'	=> $location->lat,
					'lon'	=> $location->lon,
				];
			}
		}
		ksort( $list );
		return array_values( $list );
	}
}
