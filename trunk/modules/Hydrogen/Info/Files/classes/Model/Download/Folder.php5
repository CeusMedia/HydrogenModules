<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Download_Folder extends CMF_Hydrogen_Model {

	protected $name		= 'download_folders';
	protected $columns	= array(
		'downloadFolderId',
		'parentId',
		'type',
		'rank',
		'title',
		'description',
		'nrFolders',
		'nrFiles',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'downloadFolderId';
	protected $indices		= array(
		'parentId',
		'type',
		'rank',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
