<?php
/**
 *	User Token Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	User Token Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_User_Token extends CMF_Hydrogen_Model
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

	protected $name			= 'user_tokens';

	protected $columns		= array(
		'userTokenId',
		'userId',
		'status',
		'token',
		'createdAt',
		'usedAt',
		'revokedAt',
	);

	protected $primaryKey	= 'userTokenId';

	protected $indices		= array(
		'userId',
		'status',
		'token',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
