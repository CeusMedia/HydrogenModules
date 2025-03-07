<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_IP_Lock_Reason extends Entity
{
	public int|string $ipLockReasonId;
	public int $status;
	public ?int $code				= 423;
	public ?int $duration			= NULL;
	public string $title;
	public ?string $description		= NULL;
	public int $createdAt			= 0;
	public int $appliedAt			= 0;
	public ?int $unlockedAt			= NULL;
}
