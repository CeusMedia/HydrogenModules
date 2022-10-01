<?php
/**
 *	Issue Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Issue Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue_Note extends Model
{
	protected string $name			= 'issue_notes';

	protected array $columns		= array(
		'issueNoteId',
		'issueId',
		'userId',
		'note',
		'timestamp',
	);

	protected string $primaryKey	= 'issueNoteId';

	protected array $indices		= array(
		'issueId',
		'userId',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
