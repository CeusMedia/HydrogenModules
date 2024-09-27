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
	public const ACCESS_NONE		= 0;
//	public const ACCESS_			= 1;
//	public const ACCESS_			= 2;
//	public const ACCESS_			= 4;
//	public const ACCESS_			= 8;
//	public const ACCESS_			= 16;
//	public const ACCESS_			= 32;
	public const ACCESS_ACL			= 64;
	public const ACCESS_FULL		= 128;

	public const ACCESSES			= [
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

	public const REGISTER_DENIED	= 0;
//	public const REGISTER_			= 1;
//	public const REGISTER_			= 2;
//	public const REGISTER_			= 4;
//	public const REGISTER_			= 8;
//	public const REGISTER_			= 16;
	public const REGISTER_HIDDEN	= 32;
	public const REGISTER_VISIBLE	= 64;
	public const REGISTER_DEFAULT	= 128;

	public const REGISTERS			= [
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

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_Role';
}
