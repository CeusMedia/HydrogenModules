<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Undo_Log extends Model
{
	const MODE_UNKNOWN	= 0;
	const MODE_INSERT	= 1;
	const MODE_UPDATE	= 2;
	const MODE_DELETE	= 3;

	protected string $name		= 'undo_log';

	protected array $columns	= [
		'changeId',
		'userId',
		'mode',
		'tableName',
		'primaryKey',
		'values',
		'timestamp',
	];

	protected string $primaryKey	= 'changeId';

	protected array $indices		= [
		'userId',
		'mode',
		'tableName',
		'primaryKey',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
