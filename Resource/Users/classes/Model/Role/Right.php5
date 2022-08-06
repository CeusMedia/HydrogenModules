<?php
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Role_Right extends Model
{
	protected $name			= 'role_rights';

	protected $columns		= array(
		'roleRightId',
		'roleId',
		'controller',
		'action',
		'timestamp',
	);

	protected $primaryKey	= 'roleRightId';

	protected $indices		= array(
		'roleId',
		'controller',
		'action',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;

	public static function maxifyController( string $controller ): string
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	public static function minifyController( string $controller ): string
	{
		return str_replace( array( '-', '/' ), '_', strtolower( $controller ) );
	}
}
