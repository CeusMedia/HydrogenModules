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
	const STATUS_HIDDEN		= -1;
	const STATUS_NEW		= 0;
	const STATUS_PUBLIC		= 1;

	const STATUSES			= [
		self::STATUS_HIDDEN,
		self::STATUS_NEW,
		self::STATUS_PUBLIC,
	];

	protected $name		= 'news';

	protected $columns	= array(
		'newsId',
		'status',
		'type',
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
		'type',
		'title',
		'startsAt',
		'endsAt',
		'createdAt',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
