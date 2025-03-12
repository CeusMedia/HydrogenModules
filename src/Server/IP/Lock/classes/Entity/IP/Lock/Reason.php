<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Lock_Reason extends Entity
{
	public int|string $ipLockReasonId;
	public int $status				= Model_IP_Lock_Reason::STATUS_DISABLED;
	public ?int $code				= 423;
	public ?int $duration			= NULL;
	public string $title;
	public ?string $description		= NULL;
	public int $createdAt			= 0;
	public ?int $appliedAt			= NULL;
	public ?int $unlockedAt			= NULL;

	/** @var array<Entity_IP_Lock_Filter> $filters */
	public array $filters			= [];

	protected static array $mandatoryFields	= [
		'title',
	];

	protected static function checkValues( array $data ): void
	{
		if( !in_array( $data['status'], Model_IP_Lock_Reason::STATUSES, TRUE ) )
			throw new RangeException( 'Invalid status: '.$data['status'] );
	}
}
