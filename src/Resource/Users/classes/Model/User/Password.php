<?php
/**
 *	User Password Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Password Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_User_Password extends Model
{
	public const STATUS_REVOKED		= -2;
	public const STATUS_OUTDATED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_REVOKED,
		self::STATUS_OUTDATED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected string $name			= 'user_passwords';

	protected array $columns		= [
		'userPasswordId',
		'userId',
		'algo',
		'status',
		'salt',
		'hash',
		'failsLast',
		'failsTotal',
		'createdAt',
		'failedAt',
		'usedAt',
		'revokedAt',
	];

	protected string $primaryKey	= 'userPasswordId';

	protected array $indices		= [
		'userId',
		'status',
		'salt',
		'hash',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_User_Password';
}
