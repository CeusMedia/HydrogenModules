<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Download_Folder extends Model
{
	protected string $name			= 'download_folders';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'downloadFolderId';

	protected array $indices		= [
		'parentId',
		'type',
		'rank',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
