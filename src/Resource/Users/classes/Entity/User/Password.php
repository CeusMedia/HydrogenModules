<?php

class Entity_User_Password
{
	public int|string $userPasswordId;
	public int|string $userId;
	public string $algo;
	public int $status				= Model_User_Password::STATUS_NEW;
	public string $salt;
	public string $hash;
	public string $failsLast;
	public int $failsTotal			= 0;
	public string $createdAt;
	public ?string $failedAt		= NULL;
	public ?string $usedAt			= NULL;
	public ?string $revokedAt		= NULL;
}