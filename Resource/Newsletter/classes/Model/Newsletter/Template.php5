<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Template extends CMF_Hydrogen_Model
{
	const STATUS_DELETED 	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_WORK		= 0;
	const STATUS_READY		= 1;
	const STATUS_USED		= 2;

	const STATUSES			= array(
		self::STATUS_DELETED,
		self::STATUS_REJECTED,
		self::STATUS_WORK,
		self::STATUS_READY,
		self::STATUS_USED,
	);

	protected $name		= 'newsletter_templates';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'newsletterTemplateId';

	protected $indices		= array(
		'creatorId',
		'status',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
