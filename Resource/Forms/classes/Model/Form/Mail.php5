<?php
class Model_Form_Mail extends CMF_Hydrogen_Model{

	const ROLE_TYPE_NONE			= 0;
	const ROLE_TYPE_ALL				= 1;
	const ROLE_TYPE_CUSTOMER_ALL	= 11;
	const ROLE_TYPE_CUSTOMER_RESULT	= 12;
	const ROLE_TYPE_CUSTOMER_REACT	= 13;
	const ROLE_TYPE_LEADER_ALL		= 21;
	const ROLE_TYPE_LEADER_RESULT	= 22;
	const ROLE_TYPE_LEADER_REACT	= 23;
	const ROLE_TYPE_MANAGER_ALL		= 31;
	const ROLE_TYPE_MANAGER_RESULT	= 32;
	const ROLE_TYPE_MANAGER_REACT	= 33;

	const FORMAT_TEXT		= 1;
	const FORMAT_HTML		= 2;

	protected $columns		= array(
		'mailId',
		'roleType',
		'identifier',
		'format',
		'subject',
		'title',
		'content',
	);
	protected $indices		= array(
		'roleType',
		'identifier',
		'format',
	);
	protected $primaryKey	= 'mailId';
	protected $name			= 'form_mails';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
