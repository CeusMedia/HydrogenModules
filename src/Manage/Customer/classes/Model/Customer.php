<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Customer extends Model
{
	protected string $name			= 'customers';

	protected array $columns		= array(
		'customerId',
		'creatorId',
		'size',
		'type',
		'title',
		'description',
		'country',
		'city',
		'postcode',
		'street',
		'nr',
		'url',
		'longitude',
		'latitude',
		'contact',
		'email',
		'phone',
		'fax',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'customerId';

	protected $indizes		= [];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
