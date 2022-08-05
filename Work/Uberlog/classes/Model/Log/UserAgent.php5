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
	protected $name			= 'log_useragents';

	protected $columns		= array(
		'logUserAgentId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected $primaryKey	= 'logUserAgentId';

	protected $indices		= array(
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
