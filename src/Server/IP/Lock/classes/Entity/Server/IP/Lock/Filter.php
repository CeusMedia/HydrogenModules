<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Server_IP_Lock_Filter extends Entity
{
	public int|string $ipLockFilterId;
	public int|string $reasonId;
	public int $status;
	public int $lockStatus;
	public ?string $method			= NULL;
	public string $pattern;
	public int $createdAt			= 0;
	public ?int $appliedAt			= NULL;
	public ?int $modifiedAt			= NULL;
}
