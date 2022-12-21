<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Transfer_Target extends Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

	protected array $columns		= [
		'formTransferTargetId',
		'status',
		'title',
		'className',
		'baseUrl',
		'apiKey',
		'createdAt',
		'modifiedAt',
	];

	protected array $indices		= [
		'status',
		'className',
	];

	protected string $primaryKey	= 'formTransferTargetId';

	protected string $name			= 'form_transfer_targets';

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
