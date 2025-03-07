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
	public int $views				= 0;
	public int $lockedAt			= 0;
	public int $visitedAt			= 0;
	public int $unlockedAt			= 0;

	public ?Entity_IP_Lock_Filter $filter	= NULL;
	public ?Entity_IP_Lock_Reason $reason	= NULL;
	public int $unlockIn				= 0;
	public int $unlockAt				= 0;
}
