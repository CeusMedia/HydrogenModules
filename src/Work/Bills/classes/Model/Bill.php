<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Bill extends Model
{
	protected string $name			= 'bills';

	protected array $columns		= [
		'billId',
		'userId',
		'customerId',
		'type',
		'status',
		'price',
		'date',
		'title',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'billId';

	protected array $indices		= [
		'userId',
		'customerId',
		'type',
		'status',
		'date',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
