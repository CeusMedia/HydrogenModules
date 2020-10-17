<?php
/**
 *	Issue Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
/**
 *	Issue Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 Ceus Media (https://ceusmedia.de/)
 */
class Model_Issue_Note extends CMF_Hydrogen_Model
{
	protected $name			= 'issue_notes';

	protected $columns		= array(
		'issueNoteId',
		'issueId',
		'userId',
		'note',
		'timestamp',
	);

	protected $primaryKey	= 'issueNoteId';

	protected $indices		= array(
		'issueId',
		'userId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
