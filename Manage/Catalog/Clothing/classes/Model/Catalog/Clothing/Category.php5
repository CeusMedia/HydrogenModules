<?php
/**
 *	Data model of products.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of products.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Clothing_Category extends Model
{
	protected $name		= 'catalog_clothing_categories';

	protected $columns	= array(
		"categoryId",
		"status",
		"title",
		"description",
		"createdAt",
		"modifiedAt",
	);

	protected $primaryKey	= 'categoryId';

	protected $indices		= array(
		"status",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
