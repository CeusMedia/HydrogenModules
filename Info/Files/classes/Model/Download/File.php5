<?php
/**
 *	Forum Thread Topic Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Topic Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Download_File extends Model
{
	protected string $name		= 'download_files';

	protected array $columns	= array(
		'downloadFileId',
		'downloadFolderId',
		'rank',
		'size',
		'title',
		'description',
		'nrDownloads',
		'uploadedAt',
		'downloadedAt'
	);

	protected string $primaryKey	= 'downloadFileId';

	protected array $indices		= array(
		'downloadFolderId',
		'rank',
		'size',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
