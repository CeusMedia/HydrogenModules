<?php
/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Log_Client extends Model
{
	protected $name			= 'log_clients';

	protected $columns		= array(
		'logClientId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected $primaryKey	= 'logClientId';

	protected $indices		= array(
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
