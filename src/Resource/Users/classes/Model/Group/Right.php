<?php
/**
 *	Group Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Group Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Group_Right extends Model
{
	protected string $name			= 'group_rights';

	protected array $columns		= [
		'groupRightId',
		'groupId',
		'controller',
		'action',
		'timestamp',
	];

	protected string $primaryKey	= 'groupRightId';

	protected array $indices		= [
		'groupId',
		'controller',
		'action',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	string		$className		Entity class to use */
	protected string $className				= 'Entity_Group_Right';


	public static function maximizeController( string $controller ): string
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	public static function minimizeController(string $controller ): string
	{
		return str_replace( ['-', '/'], '_', strtolower( $controller ) );
	}
}
