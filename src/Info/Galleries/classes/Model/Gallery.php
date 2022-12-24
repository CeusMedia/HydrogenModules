<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Gallery extends Model
{
	protected string $name			= 'galleries';

	protected array $columns		= [
		'galleryId',
		'status',
		'rank',
		'path',
		'title',
		'description',
		'timestamp',
	];

	protected string $primaryKey	= 'galleryId';

	protected array $indices		= [
		'status',
		'rank',
		'path',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
