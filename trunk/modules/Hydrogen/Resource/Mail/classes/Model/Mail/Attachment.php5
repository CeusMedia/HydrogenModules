<?php
/**
 *	Database model of mail attachments.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Database model of mail attachments.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Mail_Attachment extends CMF_Hydrogen_Model {

	protected $name		= 'mail_attachments';
	protected $columns	= array(
		"mailAttachmentId",
		"status",
		"language",
		"className",
		"filename",
		"mimeType",
		"countAttached",
		"createdAt",
	);
	protected $primaryKey	= 'mailAttachmentId';
	protected $indices		= array(
		"status",
		"language",
		"className",
		"filename",
		"mimeType",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
