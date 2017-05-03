<?php
/**
 *	Data Model of Customers.
 *	@category		Hydrogen.Modules
 *	@package		Resource.File
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			03.05.2017
 */
/**
 *	Data Model of Customers.
 *	@category		Hydrogen.Modules
 *	@package		Resource.File
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			03.05.2017
 */
class Model_File extends CMF_Hydrogen_Model {

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
?>
