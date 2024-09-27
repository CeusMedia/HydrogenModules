<?php
/**
 *	Group Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Group Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Group extends Model
{
	public const STATUS_DISABLED	= -2;
	public const STATUS_NEW			= 0;
	public const STATUS_ENABLED		= 1;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_NEW,
		self::STATUS_ENABLED,
	];

	public const STATUS_TRANSITIONS	= [
		self::STATUS_DISABLED		=> [
			self::STATUS_NEW,
			self::STATUS_ENABLED,
		],
		self::STATUS_NEW	=> [
			self::STATUS_DISABLED,
			self::STATUS_ENABLED,
		],
		self::STATUS_ENABLED		=> [
			self::STATUS_DISABLED,
		],
	];

	protected string $name			= 'groups';

	protected array $columns		= [
		'groupId',
		'accountId',
		'leaderId',
		'companyId',
		'status',
		'email',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'groupId';

	protected array $indices		= [
		'accountId',
		'leaderId',
		'companyId',
		'status',
		'email',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_Group';
}
