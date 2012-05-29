<?php
class Model_Company extends CMF_Hydrogen_Model{
	protected $name			= 'companies';
	protected $columns		= array(
		'companyId',
		'status',
		'title',
		'sector',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'url',
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