<?php
/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue_Change extends Model
{
	const TYPE_REPORTER		= 1;
	const TYPE_MANAGER		= 2;
	const TYPE_PROJECT		= 3;
	const TYPE_TYPE			= 4;
	const TYPE_SEVERITY		= 5;
	const TYPE_PRIORITY		= 6;
	const TYPE_STATUS		= 7;
	const TYPE_PROGRESS		= 8;
	const TYPE_DESCRIPTION	= 9;
	const TYPE_NOTE			= 10;
	const TYPE_ATTACHMENT	= 11;
	const TYPE_PATCH		= 12;



	protected string $name			= 'issue_changes';

	protected array $columns		= [
		'issueChangeId',
		'issueId',
		'userId',
		'noteId',
		'type',
		'from',
		'to',
		'timestamp',
	];

	protected string $primaryKey	= 'issueChangeId';

	protected array $indices		= [
		'issueId',
		'userId',
		'noteId',
		'type',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
