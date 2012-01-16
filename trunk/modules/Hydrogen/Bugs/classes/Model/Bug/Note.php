<?php
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Bug Note Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Bug_Note extends CMF_Hydrogen_Model {

	protected $name			= 'bug_notes';
	protected $columns		= array(
		'bugNoteId',
		'bugId',
		'userId',
		'note',
		'timestamp',
	);
	protected $primaryKey	= 'bugNoteId';
	protected $indices		= array(
		'bugId',
		'userId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>