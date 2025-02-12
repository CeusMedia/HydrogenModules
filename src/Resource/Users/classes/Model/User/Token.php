<?php
/**
 *	User Token Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Token Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_User_Token extends Model
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

	protected string $name			= 'user_tokens';

	protected array $columns		= [
		'userTokenId',
		'userId',
		'status',
		'scope',
		'token',
		'createdAt',
		'usedAt',
		'revokedAt',
	];

	protected string $primaryKey	= 'userTokenId';

	protected array $indices		= [
		'userId',
		'status',
		'scope',
		'token',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_User_Token';
}
