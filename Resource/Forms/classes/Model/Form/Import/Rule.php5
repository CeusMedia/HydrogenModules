<?php
class Model_Form_Import_Rule extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_TEST		= 1;
	const STATUS_ACTIVE		= 2;
	const STATUS_PAUSED		= 3;
	const STATUS_DISABLED	= 4;

	const STATUSES			= array(
		self::STATUS_NEW,
		self::STATUS_TEST,
		self::STATUS_ACTIVE,
		self::STATUS_PAUSED,
		self::STATUS_DISABLED,
	);

	const TRANSITINS_STATUS	= array(
		self::STATUS_NEW		=> array(
			self::STATUS_TEST,
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		),
		self::STATUS_TEST		=> array(
			self::STATUS_ACTIVE,
			self::STATUS_PAUSED,
			self::STATUS_DISABLED,
		),
		self::STATUS_ACTIVE		=> array(
			self::STATUS_PAUSED,
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED,
			self::STATUS_TEST,
		),
		self::STATUS_PAUSED		=> array(
			self::STATUS_ACTIVE,
			self::STATUS_DISABLED,
			self::STATUS_TEST,
		),
		self::STATUS_DISABLED		=> array(
			self::STATUS_PAUSED,
			self::STATUS_ACTIVE,
			self::STATUS_TEST,
		),
	);

	protected $columns		= array(
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
	);
	protected $indices		= array(
		'importConnectionId',
		'formId',
		'status',
	);
	protected $primaryKey	= 'formImportRuleId';
	protected $name			= 'form_import_rules';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
