<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Data Model of Shop Bridges.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop.Bridge
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */

use CeusMedia\HydrogenFramework\Model\Database\Table as DatabaseModel;

/**
 *	Data Model of Shop Bridges.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop.Bridge
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */
class Model_Shop_Bridge extends DatabaseModel
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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Shop_Bridge::class;
}
