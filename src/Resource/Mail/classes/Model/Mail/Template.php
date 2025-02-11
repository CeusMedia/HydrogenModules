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
	public const STATUS_NEW			= 0;
	public const STATUS_IMPORTED	= 1;
	public const STATUS_USABLE		= 2;
	public const STATUS_ACTIVE		= 3;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_IMPORTED,
		self::STATUS_USABLE,
		self::STATUS_ACTIVE,
	];

	protected string $name			= 'mail_templates';

	protected array $columns		= [
		'mailTemplateId',
		'status',
		'language',
		'title',
		'plain',
		'html',
		'css',
		'styles',
		'images',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'mailTemplateId';

	protected array $indices		= [
		'status',
		'language',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= 'Entity_Mail_Template';
}
