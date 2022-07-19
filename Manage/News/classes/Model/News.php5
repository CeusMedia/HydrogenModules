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
class Model_News extends Model
{
	protected $name		= 'news';

	protected $columns	= array(
		'newsId',
		'status',
		'title',
		'content',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
	);

	protected $primaryKey	= 'newsId';

	protected $indices		= array(
		'status',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
