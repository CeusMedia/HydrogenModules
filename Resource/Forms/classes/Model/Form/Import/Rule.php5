<?php
class Model_Form_Import_Rule extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	protected $columns		= array(
		'formImportRuleId',
		'importConnectionId',
		'formId',
		'title',
		'searchCriteria',
		'rules',
		'moveTo',
		'renameTo',
		'createdAt',
		'modifiedAt',
	);
	protected $indices		= array(
		'importConnectionId',
		'formId',
	);
	protected $primaryKey	= 'formImportRuleId';
	protected $name			= 'form_import_rules';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
