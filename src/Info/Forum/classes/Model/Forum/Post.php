<?php
/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Forum_Post extends Model
{
	protected string $name			= 'forum_posts';

	protected array $columns		= [
		'postId',
		'threadId',
		'authorId',
		'type',
		'status',
		'content',
		'createdAt',
		'modifiedAt'
	];

	protected string $primaryKey	= 'postId';

	protected array $indices		= [
		'threadId',
		'authorId',
		'type',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
