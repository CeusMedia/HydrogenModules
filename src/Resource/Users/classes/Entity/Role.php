<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Role extends Entity
{
	public int|string $roleId;
	public int $access				= Model_Role::ACCESS_NONE;
	public int $register			= Model_Role::REGISTER_DENIED;
	public string $title;
	public ?string $description		= NULL;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;

	/** @var Entity_User[] $users */
	public array $users					= [];
}