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
class Model_Bug_Change extends CMF_Hydrogen_Model
{
	protected $name			= 'bug_changes';

	protected $columns		= array(
		'bugChangeId',
		'bugId',
		'userId',
		'noteId',
		'type',
		'from',
		'to',
		'timestamp',
	);

	protected $primaryKey	= 'bugChangeId';

	protected $indices		= array(
		'bugId',
		'userId',
		'noteId',
		'type',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
