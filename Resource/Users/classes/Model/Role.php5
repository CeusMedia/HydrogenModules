<?php
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Role extends CMF_Hydrogen_Model
{
	const ACCESS_NONE		= 0;
//	const ACCESS_			= 1;
//	const ACCESS_			= 2;
//	const ACCESS_			= 4;
//	const ACCESS_			= 8;
//	const ACCESS_			= 16;
//	const ACCESS_			= 32;
	const ACCESS_ACL		= 64;
	const ACCESS_FULL		= 128;

	const ACCESSES			= array(
		self::ACCESS_NONE,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
		self::ACCESS_ACL,
		self::ACCESS_FULL,
	);

	const REGISTER_DENIED	= 0;
//	const REGISTER_			= 1;
//	const REGISTER_			= 2;
//	const REGISTER_			= 4;
//	const REGISTER_			= 8;
//	const REGISTER_			= 16;
	const REGISTER_HIDDEN	= 32;
	const REGISTER_VISIBLE	= 64;
	const REGISTER_DEFAULT	= 128;

	const REGISTERS			= array(
		self::REGISTER_DENIED,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
		self::REGISTER_HIDDEN,
		self::REGISTER_VISIBLE,
		self::REGISTER_DEFAULT,
	);

	protected $name		= 'roles';

	protected $columns	= array(
		'roleId',
		'access',
		'register',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'roleId';

	protected $indices		= array(
		'access',
		'register',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
