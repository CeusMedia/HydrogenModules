<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
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

	public const GENDER_FEMALE			= 0;
	public const GENDER_MALE			= 1;
	public const GENDER_OTHERS			= 2;
	public const GENDERS				= [
		self::GENDER_FEMALE,
		self::GENDER_MALE,
		self::GENDER_OTHERS,
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
		'tester',
		'registeredAt',
	];

	protected string $primaryKey	= 'newsletterReaderId';

	protected array $indices		= [
		'status',
		'email',
		'firstname',
		'surname',
		'tester',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className			= Entity_Newsletter_Reader::class;
}
