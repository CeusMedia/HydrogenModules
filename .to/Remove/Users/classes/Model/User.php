<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_User extends Model
{
	protected string $name		= 'users';

	protected array $columns	= [
		'userId',
		'roleId',
		'roomId',
		'companyId',
		'status',
		'email',
		'username',
		'password',
		'gender',
		'salutation',
		'firstname',
		'surname',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'createdAt',
		'modifiedAt',
		'loggedAt',
		'activeAt',
	];

	protected string $primaryKey	= 'userId';

	protected array $indices		= [
		'roleId',
		'roomId',
		'companyId',
		'status',
		'username',
		'email',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
