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
	protected $name		= 'galleries';

	protected $columns	= array(
		'galleryId',
		'status',
		'rank',
		'path',
		'title',
		'description',
		'timestamp',
	);

	protected $primaryKey	= 'galleryId';

	protected $indices		= array(
		'status',
		'rank',
		'path',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
