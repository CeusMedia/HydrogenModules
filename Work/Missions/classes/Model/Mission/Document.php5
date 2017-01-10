<?php
/**
 *	Model.
 *	@version		$Id$
 */
/**
 *	Model.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
/*
DROP TABLE IF EXISTS `mission_documents`;
CREATE TABLE IF NOT EXISTS `mission_documents` (
  `missionDocumentId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `missionId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `size` decimal(12,0) unsigned NOT NULL,
  `mimeType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hashname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` decimal(12,0) unsigned NOT NULL,
  `modifiedAt` decimal(12,0) unsigned DEFAULT NULL,
  `accessedAt` decimal(12,0) unsigned DEFAULT NULL,
  PRIMARY KEY (`missionDocumentId`),
  KEY `missionId` (`missionId`),
  KEY `userId` (`userId`),
  KEY `mimeType` (`mimeType`),
  KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/
class Model_Mission_Document extends CMF_Hydrogen_Model{

	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "mission_documents";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'missionDocumentId',
		'missionId',
		'userId',
		'mimetype',
		'size',
		'filename',
		'hashname',
		'createdAt',
		'modifiedAt',
		'accessedAt',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "missionDocumentId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'missionId',
		'userId',
		'mimetype',
		'filename',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
