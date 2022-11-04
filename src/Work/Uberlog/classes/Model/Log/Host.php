<?php
/**
 *	Uberlog Host Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog Host Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Log_Host extends Model
{
	protected string $name			= 'log_hosts';

	protected array $columns		= array(
		'logHostId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected string $primaryKey	= 'logHostId';

	protected array $indices		= array(
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
