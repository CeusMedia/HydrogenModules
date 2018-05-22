<?php
class Model_Form_Mail extends CMF_Hydrogen_Model{

	const FORMAT_TEXT		= 1;
	const FORMAT_HTML		= 2;

	protected $columns		= array(
		'mailId',
		'identifier',
		'format',
		'subject',
		'title',
		'content',
	);
	protected $indices		= array(
		'identifier',
		'format',
	);
	protected $primaryKey	= 'mailId';
	protected $name			= 'form_mails';
	protected $fetchMode	= PDO::FETCH_OBJ;
}

