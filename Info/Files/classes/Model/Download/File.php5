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
	protected $name		= 'download_files';

	protected $columns	= array(
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

	protected $primaryKey	= 'downloadFileId';

	protected $indices		= array(
		'downloadFolderId',
		'rank',
		'size',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
