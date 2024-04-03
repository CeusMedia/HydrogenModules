<?php
/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Role_Right extends Model
{
	protected string $name		= 'role_rights';

	protected array $columns	= [
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

	protected int $fetchMode	= PDO::FETCH_OBJ;

	public static function maxifyController( $controller )
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	public static function minifyController( $controller )
	{
		return str_replace( ['-', '/'], '_', strtolower( $controller ) );
	}
}
