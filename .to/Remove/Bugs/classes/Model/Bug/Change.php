<?php
/**
 *	Bug Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Bug Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Bug_Change extends Model
{
	protected string $name			= 'bug_changes';

	protected array $columns		= [
		'bugChangeId',
		'bugId',
		'userId',
		'noteId',
		'type',
		'from',
		'to',
		'timestamp',
	];

	protected string $primaryKey	= 'bugChangeId';

	protected array $indices		= [
		'bugId',
		'userId',
		'noteId',
		'type',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
