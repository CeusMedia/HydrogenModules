<?php
/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Log_UserAgent extends Model
{
	protected string $name			= 'log_useragents';

	protected array $columns		= [
		'logUserAgentId',
		'title',
		'createdAt',
		'loggedAt',
	];

	protected string $primaryKey	= 'logUserAgentId';

	protected array $indices		= [
		'title',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
