<?php
/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue extends Model
{
	const PRIORITY_UNKNOWN			= 0;
	const PRIORITY_NECESSARY		= 1;
	const PRIORITY_IMPORTANT		= 2;
	const PRIORITY_NORMAL			= 3;
	const PRIORITY_DISPENSABLE		= 4;
	const PRIORITY_FUTILE			= 5;

	const TYPE_ERROR		= 0;
	const TYPE_TASK			= 1;
	const TYPE_REQUEST		= 2;

	const SEVERITY_UNKNOWN		= 0;
	const SEVERITY_CRITICAL		= 1;
	const SEVERITY_NORMAL		= 2;
	const SEVERITY_MINOR		= 3;

	const STATUS_NEW			= 0;
	const STATUS_ASSIGNED		= 1;
	const STATUS_ACCEPTED		= 2;
	const STATUS_PROGRESSING	= 3;
	const STATUS_READY			= 4;
	const STATUS_REOPENED		= 5;
	const STATUS_CLOSED			= 6;

	protected string $name			= 'issues';

	protected array $columns		= [
		'issueId',
		'reporterId',
		'managerId',
		'projectId',
		'type',
		'severity',
		'priority',
		'status',
		'progress',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'issueId';

	protected array $indices		= [
		'reporterId',
		'managerId',
		'projectId',
		'type',
		'severity',
		'priority',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
