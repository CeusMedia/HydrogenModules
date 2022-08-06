<?php
/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */
class Model_User extends Model
{
	protected $name		= 'users';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'userId';

	protected $indices		= array(
		'roleId',
		'roomId',
		'companyId',
		'status',
		'username',
		'email',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
