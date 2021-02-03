<?php
class Model_Form_Transfer_Rule extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	protected $columns		= array(
		'formTransferRuleId',
		'formTransferTargetId',
		'formId',
		'title',
		'rules',
		'createdAt',
		'modifiedAt',
	);
	protected $indices		= array(
		'formTransferTargetId',
		'formId',
	);
	protected $primaryKey	= 'formTransferRuleId';
	protected $name			= 'form_transfer_rules';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
