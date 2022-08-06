<?php
/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Oauth_Provider extends Model
{
	const STATUS_INACTIVE		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;

	const STATUSES				= [
		self::STATUS_INACTIVE,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected $name		= 'oauth_providers';

	protected $columns	= array(
		'oauthProviderId',
		'status',
		'rank',
		'clientId',
		'clientSecret',
		'composerPackage',
		'className',
		'options',
		'scopes',
		'title',
		'icon',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'oauthProviderId';

	protected $indices		= array(
		'status',
		'clientId',
		'clientSecret',
		'composerPackage',
		'className',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
