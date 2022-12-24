<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_News extends Model
{
	protected string $name			= 'news';

	protected array $columns		= [
		'newsId',
		'status',
		'title',
		'content',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
	];

	protected string $primaryKey	= 'newsId';

	protected array $indices		= [
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
