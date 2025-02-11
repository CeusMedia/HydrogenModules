<?php
/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Forum_Thread extends Model
{
	protected string $name			= 'forum_threads';

	protected array $columns		= [
		'threadId',
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
		'createdAt',
		'modifiedAt'
	];

	protected string $primaryKey	= 'threadId';

	protected array $indices		= [
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
