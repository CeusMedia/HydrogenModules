<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Transfer_Target extends Model
{
	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;

	public const STATUSES			= [
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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Form_Transfer_Target::class;
}
