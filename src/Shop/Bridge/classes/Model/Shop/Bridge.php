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
	protected string $name			= 'shop_bridges';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'bridgeId';

	protected array $indices		= [
		'class',
		'frontendController',
		'frontendUriPath',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
