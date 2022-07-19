<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Group extends Model
{
	const STATUS_DISCARDED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_USABLE		= 1;

	const STATUSES			= array(
		self::STATUS_DISCARDED,
		self::STATUS_NEW,
		self::STATUS_USABLE,
	);

	const TYPE_DEFAULT		= 0;
	const TYPE_TEST			= 1;
	const TYPE_AUTOMATIC	= 2;
	const TYPE_HIDDEN		= 3;

	const TYPES				= array(
		self::TYPE_DEFAULT,
		self::TYPE_TEST,
		self::TYPE_AUTOMATIC,
		self::TYPE_HIDDEN,
	);

	protected $name		= 'newsletter_groups';

	protected $columns	= array(
		'newsletterGroupId',
		'creatorId',
		'status',
		'type',
		'title',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'newsletterGroupId';

	protected $indices		= array(
		'creatorId',
		'status',
		'type',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
