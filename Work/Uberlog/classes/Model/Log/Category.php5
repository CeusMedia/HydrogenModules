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
	protected $name			= 'log_categories';

	protected $columns		= array(
		'logCategoryId',
		'title',
		'createdAt',
		'loggedAt',
	);

	protected $primaryKey	= 'logCategoryId';

	protected $indices		= array(
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
