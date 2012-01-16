<?php
/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Bug extends CMF_Hydrogen_Model {

	protected $name			= 'bugs';
	protected $columns		= array(
		'bugId',
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
		'progress',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'bugId';
	protected $indices		= array(
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>