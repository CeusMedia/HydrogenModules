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
	const STATUS_REVOKED	= -2;
	const STATUS_OUTDATED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVE		= 1;

	const STATUSES			= array(
		self::STATUS_REVOKED,
		self::STATUS_OUTDATED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	);

	protected $name			= 'user_passwords';

	protected $columns		= array(
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
	);

	protected $primaryKey	= 'userPasswordId';

	protected $indices		= array(
		'userId',
		'status',
		'salt',
		'hash',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
