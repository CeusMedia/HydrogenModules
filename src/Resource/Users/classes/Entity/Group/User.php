<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Group_User extends Entity
{
	public int|string $groupUserId;
	public int|string $groupId;
	public int|string $userId;
	public int $status				= Model_Group_User::STATUS_UNCONFIRMED;
	public string $timestamp;
}