<?php
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Oauth_User extends Model
{
	protected string $name			= 'oauth_users';

	protected array $columns		= [
		'oauthUserId',
		'oauthProviderId',
		'oauthId',
		'localUserId',
		'timestamp',
	];

	protected string $primaryKey	= 'oauthUserId';

	protected array $indices		= [
		'oauthProviderId',
		'oauthId',
		'localUserId',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
