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
class Model_Backup extends CMF_Hydrogen_Model
{
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
