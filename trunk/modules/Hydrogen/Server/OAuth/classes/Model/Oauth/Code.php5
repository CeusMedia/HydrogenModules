<?php
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
DROP TABLE IF EXISTS `<%?prefix%>oauth_codes`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>oauth_codes` (
  `oauthCodeId` int(11) NOT NULL AUTO_INCREMENT,
  `oauthApplicationId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `redirectUri` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `scope` text COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` decimal(12,0) unsigned NOT NULL,
  PRIMARY KEY (`oauthCodeId`),
  KEY `oauthApplicationId` (`oauthApplicationId`),
  KEY `redirectUri` (`redirectUri`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class Model_Oauth_Code extends CMF_Hydrogen_Model {

	protected $name		= 'oauth_codes';
	protected $columns	= array(
		'oauthCodeId',
		'oauthApplicationId',
		'redirectUri',
		'code',
		'scope',
		'createdAt',
	);
	protected $primaryKey	= 'oauthCodeId';
	protected $indices		= array(
		'oauthApplicationId',
		'redirectUri',
		'code',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	/**
	 *	Returnes generated unique token.
	 *	@access		public
	 *	@param		integer		$applicationId		ID of application to get token for
	 *	@param		string		$scope				List of scopes to get token for (optional)
	 *	@return		string		Authorization code (32 characters)
	 */
	public function getNewCode( $applicationId, $scope = "" ){
		do{
			$code	= md5( $applicationId.'_'.$scope.'_'.microtime( TRUE ) );
		}
		while( $this->getByIndex( 'code', $code ) );
		return $code;
	}
}
?>
