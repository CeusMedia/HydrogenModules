<?php
/**
 *	OAuth Access Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Access Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
/**
DROP TABLE IF EXISTS `<%?prefix%>oauth_access_tokens`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>oauth_access_tokens` (
  `oauthAccessTokenId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oauthApplicationId` int(10) unsigned NOT NULL,
  `userId` int(11) unsigned DEFAULT NULL,
  `token` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `scope` text COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` decimal(12,0) unsigned NOT NULL,
  PRIMARY KEY (`oauthAccessTokenId`),
  KEY `oauthApplicationId` (`oauthApplicationId`),
  KEY `userId` (`userId`),
  KEY `token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class Model_Oauth_AccessToken extends Model
{
	protected string $name			= 'oauth_access_tokens';

	protected array $columns		= [
		'oauthAccessTokenId',
		'oauthApplicationId',
		'token',
		'userId',
		'scope',
		'createdAt',
	];

	protected string $primaryKey	= 'oauthAccessTokenId';

	protected array $indices		= [
		'oauthApplicationId',
		'userId',
		'token',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	Returns generated unique token.
	 *	@access		public
	 *	@param		integer			$applicationId		ID of application to get token for
	 *	@param		string			$scope				List of scopes to get token for (optional)
	 *	@param		string|NULL		$salt				Token hash salt (optional)
	 *	@param		string|NULL		$pepper				Token hash pepper (optional)
	 *	@return		string			Token (32 characters)
	 */
	public function getNewToken( $applicationId, string $scope = '', ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		do{
			$key	= $applicationId.'_'.$scope.'_'.$salt.'_'.microtime( TRUE ).'_'.$pepper;
			$token	= md5( $key );
		} while( $this->getByIndex( 'token', $token ) );
		return $token;
	}
}
