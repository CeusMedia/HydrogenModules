<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_Newsletter extends CMF_Hydrogen_Model {

	const STATUS_ABORTED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_READY			= 1;
	const STATUS_SENT			= 2;

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
?>
