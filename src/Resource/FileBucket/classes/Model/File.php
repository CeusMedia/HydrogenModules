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
	protected string $name		= 'files';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'fileId';

	protected array $indices		= array(
		"creatorId",
		"moduleId",
		"hash",
		"mimeType",
		"fileSize",
		"filePath",
		"fileName",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
