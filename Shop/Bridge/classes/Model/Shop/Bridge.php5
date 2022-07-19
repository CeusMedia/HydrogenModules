<?php
/**
 *	Data Model of Shop Bridges.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shop Bridges.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Shop_Bridge extends Model
{
	protected $name		= 'shop_bridges';

	protected $columns	= array(
		'bridgeId',
		'title',
		'class',
		'frontendController',
		'frontendUriPath',
		'backendController',
		'backendUriPath',
		'articleTableName',
		'articleIdColumn',
		'createdAt',
	);

	protected $primaryKey	= 'bridgeId';

	protected $indices		= array(
		'class',
		'frontendController',
		'frontendUriPath',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
