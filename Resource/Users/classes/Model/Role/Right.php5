<?php
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Role Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Role_Right extends CMF_Hydrogen_Model
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

	static public function maxifyController( string $controller ): string
	{
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $controller ) ) );
	}

	static public function minifyController( string $controller ): string
	{
		return str_replace( array( '-', '/' ), '_', strtolower( $controller ) );
	}
}
