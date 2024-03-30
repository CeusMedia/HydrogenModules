<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021 Ceus Media
 */
class Model_Import_Connection extends Model
{
	public const AUTH_TYPE_NONE		= 0;
	public const AUTH_TYPE_LOGIN	= 1;
	public const AUTH_TYPE_KEY		= 2;

	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;

	protected string $name			= 'import_connections';

	protected array $columns		= [
		'importConnectionId',
		'importConnectorId',
		'creatorId',
		'status',
		'hostName',
		'hostPort',
		'hostPath',
		'authType',
		'authKey',
		'authUsername',
		'authPassword',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'importConnectionId';

	protected array $indices		= [
		'importConnectorId',
		'creatorId',
		'status',
		'hostName',
		'hostPort',
		'authType',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
