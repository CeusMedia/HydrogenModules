<?php
/**
 *	Database model of mail templates.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Database model of mail templates.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Template extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_IMPORTED	= 1;
	const STATUS_USABLE		= 2;
	const STATUS_ACTIVE		= 3;

	const STATUSES			= array(
		self::STATUS_NEW,
		self::STATUS_IMPORTED,
		self::STATUS_USABLE,
		self::STATUS_ACTIVE,
	);

	protected $name		= 'mail_templates';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'mailTemplateId';

	protected $indices		= array(
		"status",
		"language",
		"title",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
