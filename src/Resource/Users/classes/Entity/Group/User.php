<?php

class Entity_Group_User
{
	public int|string $groupUserId;
	public int|string $groupId;
	public int|string $userId;
	public int $status				= Model_Group_User::STATUS_UNCONFIRMED;
	public string $timestamp;
}