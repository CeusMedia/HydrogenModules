<?php
class Model_Form_Transfer_Rule extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_CONFIRMED,
		self::STATUS_HANDLED,
	];

	protected $columns		= [
		'formTransferRuleId',
		'formTransferTargetId',
		'formId',
		'title',
		'rules',
		'createdAt',
		'modifiedAt',
	];

	protected $indices		= [
		'formTransferTargetId',
		'formId',
	];

	protected $primaryKey	= 'formTransferRuleId';

	protected $name			= 'form_transfer_rules';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
