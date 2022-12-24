<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Postcode extends Model
{
	protected string $name			= 'postcodes';

	protected array $columns		= [
		'postcodeId',
		'postcode',
		'city',
		'latitude',
		'longitude',
	];

	protected string $primaryKey	= 'postcodeId';

	protected array $indices		= [
		'postcode',
		'city',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
