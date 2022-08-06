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
	protected $name			= 'log_hosts';

	protected $columns		= array(
		'logHostId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected $primaryKey	= 'logHostId';

	protected $indices		= array(
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
