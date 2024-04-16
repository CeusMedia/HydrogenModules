<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Import_Connector extends Model
{
	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;

	public const FEATURE_INDEX		= 1;
	public const FEATURE_READ		= 2;
	public const FEATURE_RENAME		= 4;
	public const FEATURE_MOVE		= 8;
	public const FEATURE_DELETE		= 16;
	public const FEATURE_CREATE		= 32;

	protected string $name			= 'import_connectors';

	protected array $columns		= [
		'importConnectorId',
		'creatorId',
		'status',
		'className',
		'type',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'importConnectorId';

	protected array $indices		= [
		'creatorId',
		'status',
		'type',
		'className',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
