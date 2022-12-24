<?php
/**
 *	Data Model of ....
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of ....
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Event extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_REVOKED	= 1;
	const STATUS_IGNORED	= 2;
	const STATUS_RUNNING	= 3;
	const STATUS_FAILED		= 4;
	const STATUS_SUCCEEDED	= 5;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_REVOKED,
		self::STATUS_IGNORED,
		self::STATUS_RUNNING,
		self::STATUS_FAILED,
		self::STATUS_SUCCEEDED,
	];

	const STATUSES_TRANSITIONS	= [
		self::STATUS_NEW	=> [
			self::STATUS_REVOKED,
			self::STATUS_IGNORED,
			self::STATUS_RUNNING,
		],
		self::STATUS_REVOKED	=> [
			self::STATUS_NEW,
		],
		self::STATUS_IGNORED	=> [
			self::STATUS_NEW,
		],
		self::STATUS_RUNNING	=> [
			self::STATUS_FAILED,
			self::STATUS_SUCCEEDED,
		]
	];

	/** @var	array		$columns */
	protected array $columns		= [
		'eventId',
		'creatorId',
		'status',
		'scope',
		'identifier',
		'origin',
		'handler',
		'data',
		'result',
		'createdAt',
		'modifiedAt',
	];

	/** @var	integer		$fetchMode */
	protected int $fetchMode		= PDO::FETCH_OBJ;

	/** @var	array		$indices */
	protected array $indices		= [
		'creatorId',
		'status',
		'scope',
		'identifier',
		'origin',
		'handler',
	];

	/** @var	string		$name */
	protected string $name			= 'events';

	/** @var	string		$primaryKey */
	protected string $primaryKey	= 'eventId';
}
