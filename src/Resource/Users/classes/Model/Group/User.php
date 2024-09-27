<?php
/**
 *	User Group Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Group Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

class Model_Group_User extends Model
{
	public const STATUS_REVOKED		= -2;
	public const STATUS_REJECTED	= -1;
	public const STATUS_UNCONFIRMED	= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_REVOKED,
		self::STATUS_REJECTED,
		self::STATUS_UNCONFIRMED,
		self::STATUS_ACTIVE,
	];

	protected string $name			= 'group_users';

	protected array $columns		= [
		'groupUserId',
		'userId',
		'groupId',
		'status',
		'timestamp',
	];

	protected string $primaryKey	= 'groupUserId';

	protected array $indices		= [
		'userId',
		'groupId',
		'status',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_Group_User';
}