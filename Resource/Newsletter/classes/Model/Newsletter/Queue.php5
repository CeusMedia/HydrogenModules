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
class Model_Newsletter_Queue extends CMF_Hydrogen_Model
{
	const STATUS_REJECTED	= -2;
	const STATUS_CANCELLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_RUNNING	= 1;
	const STATUS_DONE		= 2;

	const STATUSES			= array(
		self::STATUS_REJECTED,
		self::STATUS_CANCELLED,
		self::STATUS_NEW,
		self::STATUS_RUNNING,
		self::STATUS_DONE,
	);

	protected $name		= 'newsletter_queues';

	protected $columns	= array(
		'newsletterQueueId',
		'newsletterId',
		'creatorId',
		'status',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'newsletterQueueId';

	protected $indices		= array(
		'newsletterId',
		'creatorId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
