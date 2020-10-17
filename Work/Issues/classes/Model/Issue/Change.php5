<?php
/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Issue Change Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue_Change extends CMF_Hydrogen_Model
{
	protected $name			= 'issue_changes';

	protected $columns		= array(
		'issueChangeId',
		'issueId',
		'userId',
		'noteId',
		'type',
		'from',
		'to',
		'timestamp',
	);

	protected $primaryKey	= 'issueChangeId';

	protected $indices		= array(
		'issueId',
		'userId',
		'noteId',
		'type',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
