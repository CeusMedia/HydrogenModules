<?php
/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Provider Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
DROP TABLE IF EXISTS `<%?prefix%>oauth_providers`;
CREATE TABLE IF NOT EXISTS `<%?prefix%>oauth_providers` (
  `oauthProviderId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `rank` tinyint(1) unsigned NOT NULL,
  `clientId` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `clientSecret` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `className` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` decimal(12,0) unsigned NOT NULL,
  `modifiedAt` decimal(12,0) unsigned DEFAULT '0',
  PRIMARY KEY (`oauthProviderId`),
  UNIQUE KEY `clientId` (`clientId`),
  UNIQUE KEY `title` (`title`),
  UNIQUE KEY `className` (`className`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class Model_Oauth_Provider extends CMF_Hydrogen_Model {

	protected $name		= 'oauth_providers';
	protected $columns	= array(
		'oauthProviderId',
		'status',
		'rank',
		'clientId',
		'clientSecret',
		'className',
		'options',
		'title',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'oauthProviderId';
	protected $indices		= array(
		'status',
		'clientId',
		'clientSecret',
		'className',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
