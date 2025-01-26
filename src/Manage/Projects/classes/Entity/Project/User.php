<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Project_User extends Entity
{
	public int|string $projectUserId;
	public int|string $projectId;
	public int|string $creatorId	= 0;
	public int|string $userId;
	public int $status				= 0;
	public bool $isDefault			= FALSE;
	public int $createdAt			= 0;
	public ?int $modifiedAt			= NULL;

	public ?Entity_User $user		= NULL;
}