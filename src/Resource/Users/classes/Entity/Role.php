<?php

class Entity_Role
{
	public int|string $roleId;
	public int $access				= Model_Role::ACCESS_NONE;
	public int $register			= Model_Role::REGISTER_DENIED;
	public string $title;
	public ?string $description		= NULL;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;
}