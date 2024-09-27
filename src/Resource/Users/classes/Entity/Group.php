<?php

/** @property Entity_User[] $users */
class Entity_Group
{
	public int|string $groupId;
	public int|string $accountId;
	public int|string $leaderId;
	public int|string $companyId;
	public int $status					= Model_Group::STATUS_NEW;
	public string $title;
	public ?string $description;
	public ?string $email				= NULL;
	public string $createdAt;
	public ?string $modifiedAt			= NULL;
}