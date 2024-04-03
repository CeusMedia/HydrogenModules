<?php
/**
 *	Role Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Role Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Role extends Model
{
	const ACCESS_NONE		= 0;
	const ACCESS_ACL		= 64;
	const ACCESS_FULL		= 128;

	const REGISTER_DENIED	= 0;
	const REGISTER_HIDDEN	= 32;
	const REGISTER_VISIBLE	= 64;
	const REGISTER_DEFAULT	= 128;

	protected string $name		= 'roles';

	protected array $columns	= [
		'roleId',
		'access',
		'register',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'roleId';

	protected array $indices		= [
		'access',
		'register',
		'title',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
