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
	protected $name			= 'issue_notes';

	protected $columns		= array(
		'issueNoteId',
		'issueId',
		'userId',
		'note',
		'timestamp',
	);

	protected $primaryKey	= 'issueNoteId';

	protected $indices		= array(
		'issueId',
		'userId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
