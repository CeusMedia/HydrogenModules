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
	protected string $name			= 'tokens';

	protected array $columns		= array(
		'tokenId',
		'token',
		'ip',
		'timestamp',
	);

	protected string $primaryKey	= 'tokenId';

	protected array $indices		= array(
		'token',
		'ip'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
