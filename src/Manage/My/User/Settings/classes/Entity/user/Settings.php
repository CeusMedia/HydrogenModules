<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_User_Settings extends Entity
{
	public int|string $userSettingId;
	public string $moduleId;
	public int|string $managerId;
	public int|string $userId;
	public string $key;
	public string $value;
	public int $createdAt;
	public int $modifiedAt;
}