<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Transfer_Rule extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_CONFIRMED	= 1;
	public const STATUS_HANDLED		= 2;

	public const STATUSES			= [
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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Form_Transfer_Rule::class;
}
