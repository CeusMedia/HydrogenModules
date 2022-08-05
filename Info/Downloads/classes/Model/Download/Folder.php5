<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Download_Folder extends Model
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
