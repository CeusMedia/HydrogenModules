<?php
/**
 *	Data Model of Author.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Author.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Author extends Model
{
	protected string $name			= 'catalog_authors';

	protected array $columns		= [
		"authorId",
		"lastname",
		"firstname",
//		"institution",
		"image",
		"reference",
		"description",
	];

	protected string $primaryKey	= 'authorId';

	protected array $indices		= [
		"lastname",
		"image",
		"reference",
//		"institution",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
