<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Transfer_Rule extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_CONFIRMED,
		self::STATUS_HANDLED,
	];

	protected array $columns		= [
		'formTransferRuleId',
		'formTransferTargetId',
		'formId',
		'title',
		'rules',
		'createdAt',
		'modifiedAt',
	];

	protected array $indices		= [
		'formTransferTargetId',
		'formId',
	];

	protected string $primaryKey	= 'formTransferRuleId';

	protected string $name			= 'form_transfer_rules';

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
