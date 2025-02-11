<?php
/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Log_Client extends Model
{
	protected string $name			= 'log_clients';

	protected array $columns		= [
		'logClientId',
		'title',
		'createdAt',
		'loggedAt',
	];

	protected string $primaryKey	= 'logClientId';

	protected array $indices		= [
		'title',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
