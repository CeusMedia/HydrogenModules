<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Database_Lock extends Entity
{
	public int|string $lockId;
	public int|string $userId;
	public string $subject;
	public string $entryId;
	public int $timestamp			= 0;

	public ?string $module			= NULL;
	public ?string $title			= NULL;
	public ?Entity_User $user		= NULL;

	public ?string $relationTitle	= NULL;
	public ?string $relationLink	= NULL;

}