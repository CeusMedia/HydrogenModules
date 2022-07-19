<?php
/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Right Model for ACL.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin.Role
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */
class Model_Role_Right extends CMF_Hydrogen_Model
{
	protected $name		= 'role_rights';

	protected $columns	= array(
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

	public static function maxifyController( $controller )
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	public static function minifyController( $controller )
	{
		return str_replace( array( '-', '/' ), '_', strtolower( $controller ) );
	}
}
