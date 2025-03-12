<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Lock extends Entity
{
	public int|string $ipLockId;
	public int|string $filterId;
	public int|string $reasonId;
	public int $status;
	public string $IP;
	public string $uri;
	public int $views						= 0;
	public ?int $lockedAt					= NULL;
	public ?int $visitedAt					= NULL;
	public ?int $unlockedAt					= NULL;

	public ?Entity_IP_Lock_Filter $filter	= NULL;
	public ?Entity_IP_Lock_Reason $reason	= NULL;

/*	@todo nice to have something like this, or a better global solution like a module Server_Log_Request?
	public ?string $request					= NULL;
	public ?string $session					= NULL;
	public ?string $cookie					= NULL;
	public ?string $client					= NULL;*/

	//  --  NOT PERSISTENT  --  //

	public int $unlockIn					= 0;
	public int $unlockAt					= 0;

	protected static array $mandatoryFields	= [
		'filterId',
		'reasonId',
		'IP',
		'uri',
		'filterId',
	];

	protected static function checkValues( array $data ): void
	{
		if( !in_array( $data['status'], Model_IP_Lock::STATUSES, TRUE ) )
			throw new RangeException( 'Invalid status: '.$data['status'] );
	}
}
