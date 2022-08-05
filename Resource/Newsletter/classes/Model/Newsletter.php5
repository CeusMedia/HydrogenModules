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
class Model_Newsletter extends Model
{
	const STATUS_ABORTED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_READY			= 1;
	const STATUS_SENT			= 2;

	const STATUSES				= array(
		self::STATUS_ABORTED,
		self::STATUS_NEW,
		self::STATUS_READY,
		self::STATUS_SENT,
	);

	protected $name		= 'newsletters';

	protected $columns	= array(
		'newsletterId',
		'newsletterTemplateId',
		'creatorId',
		'status',
		'senderAddress',
		'senderName',
		'title',
		'subject',
		'description',
		'heading',
		'generatePlain',
		'trackingCode',
		'plain',
		'html',
		'createdAt',
		'modifiedAt',
		'sentAt',
	);

	protected $primaryKey	= 'newsletterId';

	protected $indices		= array(
		'newsletterTemplateId',
		'creatorId',
		'status',
		'title',
		'subject',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
