<?php
/**
 *	Data Model of Customers.
 *	@category		Hydrogen.Modules
 *	@package		Resource.File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		Hydrogen.Modules
 *	@package		Resource.File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_File extends Model
{
	protected $name		= 'files';

	protected $columns	= array(
		"fileId",
		"creatorId",
		"moduleId",
		"hash",
		"mimeType",
		"fileSize",
		"filePath",
		"fileName",
		"createdAt",
		"modifiedAt",
		"viewedAt",
		"viewCount",
	);

	protected $primaryKey	= 'fileId';

	protected $indices		= array(
		"creatorId",
		"moduleId",
		"hash",
		"mimeType",
		"fileSize",
		"filePath",
		"fileName",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
