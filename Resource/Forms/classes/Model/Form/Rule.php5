<?php
class Model_Form_Rule extends CMF_Hydrogen_Model{

	const TYPE_CUSTOMER		= 0;
	const TYPE_MANAGER		= 1;

	protected $columns		= array(
		'formRuleId',
		'formId',
		'type',
		'rules',
		'mailAddresses',
		'mailId',
	);
	protected $indices		= array(
		'formId',
		'type',
		'rules',
		'mailId',
	);
	protected $primaryKey	= 'formRuleId';
	protected $name			= 'form_rules';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
