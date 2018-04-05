<?php
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Provider User Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
DROP TABLE IF EXISTS `<%?prefix%>oauth_users`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>oauth_users` (
  `oauthUserId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `oauthProviderId` int(11) unsigned NOT NULL,
  `oauthId` varchar(32) CHARACTER SET latin1 NOT NULL,
  `localUserId` int(11) unsigned NOT NULL,
  `timestamp` decimal(12,0) unsigned NOT NULL,
  PRIMARY KEY (`oauthUserId`),
  KEY `oauthProviderId` (`oauthProviderId`,`oauthId`,`localUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class Model_Oauth_User extends CMF_Hydrogen_Model {

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
?>
