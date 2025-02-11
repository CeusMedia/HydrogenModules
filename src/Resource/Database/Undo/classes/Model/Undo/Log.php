<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Undo_Log extends Model
{
	public const MODE_UNKNOWN	= 0;
	public const MODE_INSERT	= 1;
	public const MODE_UPDATE	= 2;
	public const MODE_DELETE	= 3;

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
