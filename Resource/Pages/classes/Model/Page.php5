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
class Model_Page extends Model
{
	const TYPE_CONTENT		= 0;
	const TYPE_BRANCH		= 1;
	const TYPE_MODULE		= 2;
	const TYPE_COMPONENT	= 3;

	const TYPES				= [
		self::TYPE_CONTENT,
		self::TYPE_BRANCH,
		self::TYPE_MODULE,
		self::TYPE_COMPONENT,
	];

	const STATUS_DISABLED	= -1;
	const STATUS_HIDDEN		= 0;
	const STATUS_VISIBLE	= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_HIDDEN,
		self::STATUS_VISIBLE,
	];

	protected $name		= 'pages';

	protected $columns	= array(
		'pageId',
		'parentId',
		'type',
		'scope',
		'status',
		'rank',
		'identifier',
		'fullpath',
		'controller',
		'action',
		'access',
		'title',
		'content',
		'format',
		'description',
		'keywords',
		'changefreq',
		'priority',
		'icon',
		'template',
		'createdAt',
		'modifiedAt'
	);

	protected $primaryKey	= 'pageId';

	protected $indices		= array(
		'parentId',
		'type',
		'scope',
		'status',
		'identifier',
		'fullpath',
		'controller',
		'action',
		'access',
		'format',
		'changefreq',
		'priority',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
