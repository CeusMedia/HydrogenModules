<?php
class Model_Company extends CMF_Hydrogen_Model{

	const STATUS_INACTIVE	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;

	protected $name			= 'companies';
	protected $columns		= array(
		'companyId',
		'status',
		'title',
		'description',
		'sector',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'url',
		'logo',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'companyId';
	protected $indices		= array(
		'status',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
