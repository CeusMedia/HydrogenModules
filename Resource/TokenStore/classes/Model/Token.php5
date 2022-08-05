<?php
/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Token extends Model
{
	protected $name			= 'tokens';

	protected $columns		= array(
		'tokenId',
		'token',
		'ip',
		'timestamp',
	);

	protected $primaryKey	= 'tokenId';

	protected $indices		= array(
		'token',
		'ip'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
