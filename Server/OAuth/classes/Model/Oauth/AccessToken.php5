<?php
/**
 *	OAuth Access Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Access Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
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
class Model_Oauth_AccessToken extends CMF_Hydrogen_Model {

	protected $name		= 'oauth_access_tokens';
	protected $columns	= array(
		'oauthAccessTokenId',
		'oauthApplicationId',
		'token',
		'userId',
		'scope',
		'createdAt',
	);
	protected $primaryKey	= 'oauthAccessTokenId';
	protected $indices		= array(
		'oauthApplicationId',
		'userId',
		'token',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	/**
	 *	Returnes generated unique token.
	 *	@access		public
	 *	@param		integer		$applicationId		ID of application to get token for
	 *	@param		string		$scope				List of scopes to get token for (optional)
	 *	@param		string		$salt				Token hash salt (optional)
	 *	@param		string		$pepper				Token hash pepper (optional)
	 *	@return		string		Token (32 characters)
	 */
	public function getNewToken( $applicationId, $scope = "", $salt = NULL, $pepper = NULL ){
		do{
			$key	= $applicationId.'_'.$scope.'_'.$salt.'_'.microtime( TRUE ).'_'.$pepper;
			$token	= md5( $key );
		}
		while( $this->getByIndex( 'token', $token ) );
		return $token;
	}
}
?>
