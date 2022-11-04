<?php
/**
 *	Database model of mail attachments.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Database model of mail attachments.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Attachment extends Model
{
	const STATUS_INACTIVE	= 0;
	const STATUS_ACTIVE		= 1;

	const STATUSES			= array(
		self::STATUS_INACTIVE,
		self::STATUS_ACTIVE,
	);

	protected string $name		= 'mail_attachments';

	protected array $columns	= array(
		"mailAttachmentId",
		"status",
		"language",
		"className",
		"filename",
		"mimeType",
		"countAttached",
		"createdAt",
	);

	protected string $primaryKey	= 'mailAttachmentId';

	protected array $indices		= array(
		"status",
		"language",
		"className",
		"filename",
		"mimeType",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
