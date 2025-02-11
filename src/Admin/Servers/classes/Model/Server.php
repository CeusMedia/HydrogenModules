<?php
/**
 *	General server model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	General server model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Server extends Model
{
	protected string $name			= 'servers';

	protected array $columns		= [
		'serverId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'serverId';

	protected array $indices		= [
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
