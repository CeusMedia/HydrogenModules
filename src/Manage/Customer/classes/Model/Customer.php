<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Customer extends Model
{
	protected string $name			= 'customers';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'customerId';

	protected array $indizes		= [];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
