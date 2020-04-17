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
class Model_Newsletter_Reader_Letter extends CMF_Hydrogen_Model {

	const STATUS_REMOVED	= -3;
	const STATUS_CANCELLED	= -2;
	const STATUS_FAILED		= -1;
	const STATUS_ENQUEUED	= 0;
	const STATUS_SENT		= 1;
	const STATUS_OPENED		= 2;

	protected $name		= 'newsletter_reader_letters';
	protected $columns	= array(
		'newsletterReaderLetterId',
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
		'enqueuedAt',
		'sentAt',
		'openedAt',
	);
	protected $primaryKey	= 'newsletterReaderLetterId';
	protected $indices		= array(
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
