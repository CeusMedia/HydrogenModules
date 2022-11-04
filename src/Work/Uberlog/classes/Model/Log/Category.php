<?php
/**
 *	Uberlog Category Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Uberlog Category Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Log_Category extends Model
{
	protected string $name			= 'log_categories';

	protected array $columns		= array(
		'logCategoryId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected string $primaryKey	= 'logCategoryId';

	protected array $indices		= array(
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
