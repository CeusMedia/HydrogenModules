<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Template extends Model
{
	const STATUS_DELETED 	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_WORK		= 0;
	const STATUS_READY		= 1;
	const STATUS_USED		= 2;

	const STATUSES			= [
		self::STATUS_DELETED,
		self::STATUS_REJECTED,
		self::STATUS_WORK,
		self::STATUS_READY,
		self::STATUS_USED,
	];

	protected string $name			= 'newsletter_templates';

	protected array $columns		= [
		'newsletterTemplateId',
		'creatorId',
		'themeId',
		'version',
		'status',
		'title',
		'senderAddress',
		'senderName',
		'plain',
		'html',
		'style',
		'styles',
		'imprint',
		'authorName',
		'authorEmail',
		'authorUrl',
		'authorCompany',
		'license',
		'licenseUrl',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'newsletterTemplateId';

	protected array $indices		= [
		'creatorId',
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
