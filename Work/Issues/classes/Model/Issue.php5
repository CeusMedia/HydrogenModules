<?php
/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Issue Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue extends CMF_Hydrogen_Model
{
	protected $name			= 'issues';

	protected $columns		= array(
		'issueId',
		'reporterId',
		'managerId',
		'projectId',
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

	protected $primaryKey	= 'issueId';

	protected $indices		= array(
		'reporterId',
		'managerId',
		'projectId',
		'type',
		'severity',
		'priority',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
