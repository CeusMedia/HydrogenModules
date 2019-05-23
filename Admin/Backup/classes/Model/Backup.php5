<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2019 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2019 Ceus Media
 */
class Model_Page extends CMF_Hydrogen_Model {

	const TYPE_CONTENT		= 0;
	const TYPE_BRANCH		= 1;
	const TYPE_MODULE		= 2;
	const TYPE_COMPONENT	= 3;

	const STATUS_DISABLED	= -1;
	const STATUS_HIDDEN		= 0;
	const STATUS_VISIBLE	= 1;

	protected $name		= 'backups';
	protected $columns	= array(
		'backupId',
		'creatorId',
		'status',
		'comment',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'backupId';
	protected $indices		= array(
		'creatorId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
