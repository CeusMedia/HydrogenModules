<?php
/**
 *	Uberlog Record Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog Record Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
/**
 *	Types:
 *	-3		error
 *	-2		warning
 *	-1		notice
 *	 0		info
 *	 1		success
 *	 2		done
 */
class Model_Log_Record extends Model
{
	protected string $name			= 'log_records';

	protected array $columns		= array(
		'recordId',
		'logCategoryId',
		'logClientId',
		'logHostId',
		'logUserAgentId',
		'type',
		'code',
		'category',
		'source',
		'line',
		'message',
		'client',
		'timestamp'
	);

	protected string $primaryKey	= 'recordId';

	protected array $indices		= array(
		'logCategoryId',
		'logClientId',
		'logHostId',
		'logUserAgentId',
		'type',
		'category',
		'client',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
