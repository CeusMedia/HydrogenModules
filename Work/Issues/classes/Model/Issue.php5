<?php
/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Issue extends CMF_Hydrogen_Model {

	protected $name			= 'issues';
	protected $columns		= array(
		'issueId',
		'reporterId',
		'managerId',
		'projectId',
		'serverId',
		'type',
		'severity',
		'priority',
		'status',
		'progress',
		'title',
		'content',
		'location',
		'link',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'issueId';
	protected $indices		= array(
		'reporterId',
		'managerId',
		'projectId',
		'serverId',
		'type',
		'severity',
		'priority',
		'status',
		'location',
		'link',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>