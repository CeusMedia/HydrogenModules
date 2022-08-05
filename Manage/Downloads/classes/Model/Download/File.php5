<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
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
