<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Newsletter_Queue extends CMF_Hydrogen_Model {

	const STATUS_REJECTED	= -2;
	const STATUS_CANCELLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_RUNNING	= 1;
	const STATUS_DONE		= 2;

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
?>
