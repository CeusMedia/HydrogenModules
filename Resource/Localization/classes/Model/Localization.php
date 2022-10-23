<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Localization extends Model
{
	protected string $name		= 'localizations';

	protected array $columns	= array(
		'localizationId',
		'language',
		'id',
		'content',
	);

	protected string $primaryKey	= 'localizationId';

	protected array $indices		= array(
		'language',
		'id',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
