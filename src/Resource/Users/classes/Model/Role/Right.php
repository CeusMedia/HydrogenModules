<?php
/**
 *	Role Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Role_Right extends Model
{
	protected string $name			= 'role_rights';

	protected array $columns		= [
		'roleRightId',
		'roleId',
		'controller',
		'action',
		'timestamp',
	];

	protected string $primaryKey	= 'roleRightId';

	protected array $indices		= [
		'roleId',
		'controller',
		'action',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	string		$className		Entity class to use */
	protected string $className				= 'Entity_Role_Right';

	public static function maxifyController( string $controller ): string
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	public static function minifyController( string $controller ): string
	{
		return str_replace( ['-', '/'], '_', strtolower( $controller ) );
	}
}
