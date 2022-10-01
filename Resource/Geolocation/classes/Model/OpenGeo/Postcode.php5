<?php

use CeusMedia\HydrogenFramework\Model;

class Model_OpenGeo_Postcode extends Model
{
	protected string $name			= 'postcodes';

	protected array $columns		= array(
		'postcodeId',
		'postcode',
		'city',
		'latitude',
		'longitude',
	);

	protected string $primaryKey	= 'postcodeId';

	protected array $indices		= array(
		'postcode',
		'city',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
