<?php
/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Token extends Model
{
	protected string $name			= 'tokens';

	protected array $columns		= [
		'tokenId',
		'token',
		'ip',
		'timestamp',
	];

	protected string $primaryKey	= 'tokenId';

	protected array $indices		= [
		'token',
		'ip'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
