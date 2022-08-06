<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Rule extends Model
{
	const TYPE_CUSTOMER		= 0;
	const TYPE_MANAGER		= 1;
	const TYPE_ATTACHMENT	= 2;

	const TYPES				= [
		self::TYPE_CUSTOMER,
		self::TYPE_MANAGER,
		self::TYPE_ATTACHMENT,
	];

	protected $columns		= [
		'formRuleId',
		'formId',
		'type',
		'rules',
		'mailAddresses',
		'mailId',
		'filePath',
	];

	protected $indices		= [
		'formId',
		'type',
		'rules',
		'mailId',
		'filePath',
	];

	protected $primaryKey	= 'formRuleId';

	protected $name			= 'form_rules';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
