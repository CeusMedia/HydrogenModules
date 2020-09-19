<?php
/**
 *	Forum Thread Topic Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Forum Thread Topic Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Download_Folder extends CMF_Hydrogen_Model
{
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
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
