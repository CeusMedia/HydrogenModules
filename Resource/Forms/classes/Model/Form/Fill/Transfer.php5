<?php
class Model_Form_Fill_Transfer extends CMF_Hydrogen_Model
{
	const STATUS_UNKNOWN	= 0;
	const STATUS_SUCCESS	= 1;
	const STATUS_ERROR		= 2;
	const STATUS_EXCEPTION	= 3;

	protected $columns		= array(
		'formFillTransferId',
		'formId',
		'formTransferRuleId',
		'formTransferTargetId',
		'fillId',
		'status',
		'message',
		'createdAt',
	);
	protected $indices		= array(
		'formId',
		'formTransferRuleId',
		'formTransferTargetId',
		'fillId',
		'status',
	);
	protected $primaryKey	= 'formFillTransferId';
	protected $name			= 'form_fill_transfers';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
