<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Rule extends Model
{
	public const TYPE_CUSTOMER		= 0;
	public const TYPE_MANAGER		= 1;
	public const TYPE_ATTACHMENT	= 2;

	public const TYPES				= [
		self::TYPE_CUSTOMER,
		self::TYPE_MANAGER,
		self::TYPE_ATTACHMENT,
	];

	protected array $columns		= [
		'formRuleId',
		'formId',
		'type',
		'rules',
		'mailAddresses',
		'mailId',
		'filePath',
	];

	protected array $indices		= [
		'formId',
		'type',
		'rules',
		'mailId',
		'filePath',
	];

	protected string $primaryKey	= 'formRuleId';

	protected string $name			= 'form_rules';

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
