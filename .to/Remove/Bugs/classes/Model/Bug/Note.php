<?php
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
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
