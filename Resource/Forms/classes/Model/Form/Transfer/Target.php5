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

	protected $columns		= [
		'formTransferTargetId',
		'status',
		'title',
		'className',
		'baseUrl',
		'apiKey',
		'createdAt',
		'modifiedAt',
	];

	protected $indices		= [
		'status',
		'className',
	];

	protected $primaryKey	= 'formTransferTargetId';

	protected $name			= 'form_transfer_targets';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
