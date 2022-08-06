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
	protected $name		= 'localizations';

	protected $columns	= array(
		'localizationId',
		'language',
		'id',
		'content',
	);

	protected $primaryKey	= 'localizationId';

	protected $indices		= array(
		'language',
		'id',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
