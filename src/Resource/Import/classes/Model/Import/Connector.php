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

	public const TYPE_UNKNOWN		= 0;
	public const TYPE_PULL_ASYNC	= 1;
	public const TYPE_PULL_SYNC		= 2;
	public const TYPE_PUSH_POST		= 3;
	public const TYPE_PUSH_PUT		= 4;

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
		'type',
		'className',
		'title',
		'description',
		'mimeTypes',
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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Import_Connector::class;
}
