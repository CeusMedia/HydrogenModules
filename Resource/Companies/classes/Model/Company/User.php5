<?php
class Model_Company_User extends CMF_Hydrogen_Model{
	protected $name			= 'company_users';
	protected $columns		= array(
		'companyUserId',
		'companyId',
		'userId',
//		'status',
//		'role',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'companyUserId';
	protected $indices		= array(
		'companyId',
		'userId',
//		'status',
//		'role',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
