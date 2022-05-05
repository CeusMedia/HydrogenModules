<?php
class Model_Form_Import_Rule extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_TEST		= 1;
	const STATUS_ACTIVE		= 2;
	const STATUS_PAUSED		= 3;
	const STATUS_DISABLED	= 4;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_TEST,
		self::STATUS_ACTIVE,
		self::STATUS_PAUSED,
		self::STATUS_DISABLED,
	];

	const TRANSITIONS_STATUS	= [
		self::STATUS_NEW		=> [
			self::STATUS_TEST,
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		],
		self::STATUS_TEST		=> [
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		],
		self::STATUS_ACTIVE		=> [
			self::STATUS_PAUSED,
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED,
			self::STATUS_TEST,
		],
		self::STATUS_PAUSED		=> [
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

	protected $columns		= [
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

	protected $indices		= [
		'importConnectionId',
		'formId',
		'status',
	];

	protected $primaryKey	= 'formImportRuleId';

	protected $name			= 'form_import_rules';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
