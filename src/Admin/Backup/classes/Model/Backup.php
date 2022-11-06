<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2019 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2019 Ceus Media
 */
class Model_Backup extends Model
{
	protected string $name		= 'backups';

	protected array $columns	= [
		'backupId',
		'creatorId',
		'status',
		'comment',
		'createdAt',
		'modifiedAt'
	];

	protected string $primaryKey	= 'backupId';

	protected array $indices		= [
		'creatorId',
		'status',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
