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
	public const STATUS_INACTIVE	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_INACTIVE,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected string $name			= 'oauth_providers';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'oauthProviderId';

	protected array $indices		= [
		'status',
		'clientId',
		'clientSecret',
		'composerPackage',
		'className',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
