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
	protected string $name		= 'download_folders';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'downloadFolderId';

	protected array $indices		= array(
		'parentId',
		'type',
		'rank',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}