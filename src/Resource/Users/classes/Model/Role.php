<?php
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Role extends Model
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

	const ACCESSES			= [
		self::ACCESS_NONE,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
//		self::ACCESS_,
		self::ACCESS_ACL,
		self::ACCESS_FULL,
	];

	const REGISTER_DENIED	= 0;
//	const REGISTER_			= 1;
//	const REGISTER_			= 2;
//	const REGISTER_			= 4;
//	const REGISTER_			= 8;
//	const REGISTER_			= 16;
	const REGISTER_HIDDEN	= 32;
	const REGISTER_VISIBLE	= 64;
	const REGISTER_DEFAULT	= 128;

	const REGISTERS			= [
		self::REGISTER_DENIED,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
//		self::REGISTER_,
		self::REGISTER_HIDDEN,
		self::REGISTER_VISIBLE,
		self::REGISTER_DEFAULT,
	];

	protected string $name			= 'roles';

	protected array $columns		= [
		'roleId',
		'access',
		'register',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'roleId';

	protected array $indices		= [
		'access',
		'register',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
