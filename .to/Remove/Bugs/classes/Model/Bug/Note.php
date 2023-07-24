<?php
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Bug_Note extends Model
{
	protected string $name			= 'bug_notes';

	protected array $columns		= [
		'bugNoteId',
		'bugId',
		'userId',
		'note',
		'timestamp',
	];

	protected string $primaryKey	= 'bugNoteId';

	protected array $indices		= [
		'bugId',
		'userId',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
