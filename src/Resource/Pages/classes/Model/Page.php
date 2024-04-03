<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Page extends Model
{
	public const TYPE_CONTENT		= 0;
	public const TYPE_BRANCH		= 1;
	public const TYPE_MODULE		= 2;
	public const TYPE_COMPONENT		= 3;

	public const TYPES				= [
		self::TYPE_CONTENT,
		self::TYPE_BRANCH,
		self::TYPE_MODULE,
		self::TYPE_COMPONENT,
	];

	public const STATUS_DISABLED	= -1;
	public const STATUS_HIDDEN		= 0;
	public const STATUS_VISIBLE		= 1;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_HIDDEN,
		self::STATUS_VISIBLE,
	];

	protected string $name			= 'pages';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'pageId';

	protected array $indices		= [
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
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
