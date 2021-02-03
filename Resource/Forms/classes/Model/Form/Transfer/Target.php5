<?php
class Model_Form_Transfer_Target extends CMF_Hydrogen_Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	protected $columns		= array(
		'formTransferTargetId',
		'status',
		'title',
		'className',
		'baseUrl',
		'apiKey',
		'createdAt',
		'modifiedAt',
	);
	protected $indices		= array(
		'status',
		'className',
	);
	protected $primaryKey	= 'formTransferTargetId';
	protected $name			= 'form_transfer_targets';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
