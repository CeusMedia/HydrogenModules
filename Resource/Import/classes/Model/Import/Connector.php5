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
class Model_Import_Connector extends Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const FEATURE_INDEX		= 1;
	const FEATURE_READ		= 2;
	const FEATURE_RENAME	= 4;
	const FEATURE_MOVE		= 8;
	const FEATURE_DELETE	= 16;
	const FEATURE_CREATE	= 32;

	protected string $name		= 'import_connectors';

	protected array $columns	= array(
		'importConnectorId',
		'creatorId',
		'status',
		'className',
		'type',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'importConnectorId';

	protected array $indices		= array(
		'creatorId',
		'status',
		'type',
		'className',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
