<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Reader extends Model
{
	const STATUS_DEACTIVATED	= -2;
	const STATUS_UNREGISTERED	= -1;
	const STATUS_REGISTERED		= 0;
	const STATUS_CONFIRMED		= 1;

	const STATUSES				= [
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
