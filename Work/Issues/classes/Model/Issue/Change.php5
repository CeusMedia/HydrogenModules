<?php
/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue_Change extends Model
{
	protected $name			= 'issue_changes';

	protected $columns		= array(
		'issueChangeId',
		'issueId',
		'userId',
		'noteId',
		'type',
		'from',
		'to',
		'timestamp',
	);

	protected $primaryKey	= 'issueChangeId';

	protected $indices		= array(
		'issueId',
		'userId',
		'noteId',
		'type',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
