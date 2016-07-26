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
class Model_Undo_Log extends CMF_Hydrogen_Model {

	const MODE_UNKNOWN	= 0;
	const MODE_INSERT	= 1;
	const MODE_UPDATE	= 2;
	const MODE_DELETE	= 3;

	protected $name		= 'undo_log';
	protected $columns	= array(
		'changeId',
		'userId',
		'mode',
		'tableName',
		'primaryKey',
		'values',
		'timestamp',
	);
	protected $primaryKey	= 'changeId';
	protected $indices		= array(
		'userId',
		'mode',
		'tableName',
		'primaryKey',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
