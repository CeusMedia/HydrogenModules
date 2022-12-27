<?php
/**
 *	Data model of bookstore states.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of bookstore states.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Status extends Model
{
	protected string $name			= 'catalog_bookstore_states';

	protected array $columns		= [
		'statusId',
		'title',
		'available',
		'rank',
	];

	protected string $primaryKey	= 'statusId';

	protected array $indices		= [
		"available",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
