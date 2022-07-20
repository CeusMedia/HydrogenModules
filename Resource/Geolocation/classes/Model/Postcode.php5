<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Postcode extends Model
{
	protected $name			= 'postcodes';

	protected $columns		= array(
		'postcodeId',
		'postcode',
		'city',
		'latitude',
		'longitude',
	);

	protected $primaryKey	= 'postcodeId';

	protected $indices		= array(
		'postcode',
		'city',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
