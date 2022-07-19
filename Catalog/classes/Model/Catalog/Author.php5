<?php
/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Author extends Model
{
	protected $name		= 'catalog_authors';

	protected $columns	= array(
		"authorId",
		"lastname",
		"firstname",
//		"institution",
		"image",
		"reference",
		"description",
	);

	protected $primaryKey	= 'authorId';

	protected $indices		= array(
		"lastname",
		"image",
		"reference",
//		"institution",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
