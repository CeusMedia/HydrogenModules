<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Forum_Topic extends Model
{
	protected string $name			= 'forum_topics';

	protected array $columns		= [
		'topicId',
		'parentId',
		'type',
		'rank',
		'title',
		'description',
		'createdAt',
		'modifiedAt'
	];

	protected string $primaryKey	= 'topicId';

	protected array $indices		= [
		'parentId',
		'type',
		'rank',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
