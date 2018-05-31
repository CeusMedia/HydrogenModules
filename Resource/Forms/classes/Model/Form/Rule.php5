<?php
class Model_Form_Rule extends CMF_Hydrogen_Model{

	protected $columns		= array(
		'formRuleId',
		'formId',
		'rules',
		'mailAddresses',
		'mailId',
	);
	protected $indices		= array(
		'formId',
		'rules',
		'mailId',
	);
	protected $primaryKey	= 'formRuleId';
	protected $name			= 'form_rules';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
