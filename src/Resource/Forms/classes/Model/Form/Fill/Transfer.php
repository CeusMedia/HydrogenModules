<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Fill_Transfer extends Model
{
	const STATUS_UNKNOWN	= 0;
	const STATUS_SUCCESS	= 1;
	const STATUS_ERROR		= 2;
	const STATUS_EXCEPTION	= 3;

	const STATUSES			= [
		self::STATUS_UNKNOWN,
		self::STATUS_SUCCESS,
		self::STATUS_ERROR,
		self::STATUS_EXCEPTION,
	];

	protected array $columns		= [
		'formFillTransferId',
		'formId',
		'formTransferRuleId',
		'formTransferTargetId',
		'fillId',
		'status',
		'data',
		'message',
		'trace',
		'createdAt',
	];

	protected array $indices		= [
		'formId',
		'formTransferRuleId',
		'formTransferTargetId',
		'fillId',
		'status',
	];

	protected string $primaryKey	= 'formFillTransferId';

	protected string $name			= 'form_fill_transfers';

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
