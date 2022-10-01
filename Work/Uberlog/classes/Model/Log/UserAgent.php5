<?php
/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Log_UserAgent extends Model
{
	protected string $name			= 'log_useragents';

	protected array $columns		= array(
		'logUserAgentId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected string $primaryKey	= 'logUserAgentId';

	protected array $indices		= array(
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
