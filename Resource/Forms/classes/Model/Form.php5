<?php
class Model_Form extends CMF_Hydrogen_Model{

	const TYPE_NORMAL		= 0;
	const TYPE_CONFIRM		= 1;

	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVATED	= 1;

	protected $columns		= array(
		'formId',
		'mailId',
		'type',
		'status',
		'title',
		'receivers',
		'content',
		'timestamp',
	);
	protected $indices		= array(
		'mailId',
		'status',
	);
	protected $primaryKey	= 'formId';
	protected $name			= 'forms';
	protected $fetchMode	= PDO::FETCH_OBJ;
}

