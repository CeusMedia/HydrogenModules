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
class Model_Newsletter_Group extends CMF_Hydrogen_Model {

	const STATUS_DISCARDED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_USABLE		= 1;

	const TYPE_DEFAULT		= 0;
	const TYPE_TEST			= 1;
	const TYPE_AUTOMATIC	= 2;
	const TYPE_HIDDEN		= 3;

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
?>
