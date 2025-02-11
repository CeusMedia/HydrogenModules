<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Newsletter_Reader extends Model
{
	public const STATUS_DEACTIVATED		= -2;
	public const STATUS_UNREGISTERED	= -1;
	public const STATUS_REGISTERED		= 0;
	public const STATUS_CONFIRMED		= 1;

	public const STATUSES				= [
		self::STATUS_DEACTIVATED,
		self::STATUS_UNREGISTERED,
		self::STATUS_REGISTERED,
		self::STATUS_CONFIRMED,
	];

	protected string $name			= 'newsletter_readers';

	protected array $columns		= [
		'newsletterReaderId',
		'status',
		'email',
		'gender',
		'prefix',
		'firstname',
		'surname',
		'institution',
		'registeredAt',
	];

	protected string $primaryKey	= 'newsletterReaderId';

	protected array $indices		= [
		'status',
		'email',
		'firstname',
		'surname',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
