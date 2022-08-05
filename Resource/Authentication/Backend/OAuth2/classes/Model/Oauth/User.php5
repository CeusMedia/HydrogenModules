<?php
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Oauth_User extends Model
{
	protected $name		= 'oauth_users';

	protected $columns	= array(
		'oauthUserId',
		'oauthProviderId',
		'oauthId',
		'localUserId',
		'timestamp',
	);

	protected $primaryKey	= 'oauthUserId';

	protected $indices		= array(
		'oauthProviderId',
		'oauthId',
		'localUserId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
