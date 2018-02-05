<?php
class Model_Share extends CMF_Hydrogen_Model{

	const ACCESS_PUBLIC			= 0;
	const ACCESS_CAPTCHA		= 1;
	const ACCESS_LOGIN			= 2;

	protected $name				= 'shares';
	protected $columns			= array(
		'shareId',
		'status',
		'access',
		'validity',
		'moduleId',
		'relationId',
		'path',
		'uuid',
		'createdAt',
		'accessedAt',
	);
	protected $indices			= array(
		'status',
		'access',
		'validity',
		'moduleId',
		'relationId',
		'path',
		'uuid',
	);
	protected $primaryKey		= 'shareId';
	protected $fetchMode		= PDO::FETCH_OBJ;
}
?>
