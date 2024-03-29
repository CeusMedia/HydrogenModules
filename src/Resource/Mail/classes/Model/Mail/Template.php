<?php
/**
 *	Database model of mail templates.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Database model of mail templates.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Template extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_IMPORTED	= 1;
	const STATUS_USABLE		= 2;
	const STATUS_ACTIVE		= 3;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_IMPORTED,
		self::STATUS_USABLE,
		self::STATUS_ACTIVE,
	];

	protected string $name			= 'mail_templates';

	protected array $columns		= [
		"mailTemplateId",
		"status",
		"language",
		"title",
		"plain",
		"html",
		"css",
		"styles",
		"images",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'mailTemplateId';

	protected array $indices		= [
		"status",
		"language",
		"title",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
