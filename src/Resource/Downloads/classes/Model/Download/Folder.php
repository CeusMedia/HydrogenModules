<?php
/**
 *	Model for storing folders for downloadable files.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for storing folders for downloadable files.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2025 Ceus Media (https://ceusmedia.de/)
 */
class Model_Download_Folder extends Model
{
	public const TYPE_DEFAULT		= 0;

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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Download_Folder::class;
}


