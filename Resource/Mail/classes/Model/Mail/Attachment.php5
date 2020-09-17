<?php
/**
 *	Database model of mail attachments.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Database model of mail attachments.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Attachment extends CMF_Hydrogen_Model
{
	const STATUS_INACTIVE	= 0;
	const STATUS_ACTIVE		= 1;

	const STATUSES			= array(
		self::STATUS_INACTIVE,
		self::STATUS_ACTIVE,
	);

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
