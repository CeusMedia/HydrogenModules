<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Lock_Filter extends Entity
{
	public int|string $ipLockFilterId;
	public int|string $reasonId;
	public int $status				= Model_IP_Lock_Filter::STATUS_DISABLED;
	public int $lockStatus			= Model_IP_Lock_Filter::LOCK_STATUS_IMMEDIATE;
	public ?string $method			= NULL;
	public string $pattern;
	public string $title;
	public int $createdAt			= 0;
	public ?int $appliedAt			= NULL;
	public ?int $modifiedAt			= NULL;

	protected static array $mandatoryFields	= [
		'reasonId',
		'pattern',
		'title',
	];

	protected static function checkValues( array $data ): void
	{
		if( !in_array( $data['status'], Model_IP_Lock_Filter::STATUSES, TRUE ) )
			throw new RangeException( 'Invalid status: '.$data['status'] );
		if( !in_array( $data['lockStatus'], Model_IP_Lock_Filter::LOCK_STATUSES, TRUE ) )
			throw new RangeException( 'Invalid lock status: '.$data['lockStatus'] );
	}
}
