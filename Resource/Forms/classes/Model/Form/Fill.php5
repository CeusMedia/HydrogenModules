<?php
class Model_Form_Fill extends CMF_Hydrogen_Model{

	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	protected $columns		= array(
		'fillId',
		'formId',
		'status',
		'email',
		'data',
		'referer',
		'agent',
		'createdAt',
		'modifiedAt',
	);
	protected $indices		= array(
		'formId',
		'status',
		'email',
	);
	protected $primaryKey	= 'fillId';
	protected $name			= 'form_fills';
	protected $fetchMode	= PDO::FETCH_OBJ;
}

