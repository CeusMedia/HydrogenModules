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
	protected $name		= 'forum_topics';

	protected $columns	= array(
		'topicId',
		'parentId',
		'type',
		'rank',
		'title',
		'description',
		'createdAt',
		'modifiedAt'
	);

	protected $primaryKey	= 'topicId';

	protected $indices		= array(
		'parentId',
		'type',
		'rank',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
