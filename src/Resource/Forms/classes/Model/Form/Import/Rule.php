<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Import_Rule extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_TEST		= 1;
	public const STATUS_ACTIVE		= 2;
	public const STATUS_PAUSED		= 3;
	public const STATUS_DISABLED	= 4;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_TEST,
		self::STATUS_ACTIVE,
		self::STATUS_PAUSED,
		self::STATUS_DISABLED,
	];

	public const TRANSITIONS_STATUS	= [
		self::STATUS_NEW			=> [
			self::STATUS_TEST,
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		],
		self::STATUS_TEST			=> [
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		],
		self::STATUS_ACTIVE			=> [
			self::STATUS_PAUSED,
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED,
			self::STATUS_TEST,
		],
		self::STATUS_PAUSED			=> [
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED,
			self::STATUS_TEST,
		],
		self::STATUS_DISABLED		=> [
			self::STATUS_PAUSED,
			self::STATUS_ACTIVE,
			self::STATUS_TEST,
		],
	];

	protected array $columns		= [
		'formImportRuleId',
		'importConnectionId',
		'formId',
		'status',
		'title',
		'searchCriteria',
		'options',
		'rules',
		'moveTo',
		'renameTo',
		'createdAt',
		'modifiedAt',
	];

	protected array $indices		= [
		'importConnectionId',
		'formId',
		'status',
	];

	protected string $primaryKey	= 'formImportRuleId';

	protected string $name			= 'form_import_rules';

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Form_Import_Rule::class;
}
