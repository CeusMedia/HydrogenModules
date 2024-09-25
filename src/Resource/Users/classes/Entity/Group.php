<?php

class Entity_Group
{
	public int|string $groupId;
	public int|string $accountId;
	public int|string $leaderId;
	public int|string $companyId;
	public int $status;
	public string $email;
	public string $createdAt;
	public ?string $modifiedAt			= NULL;
}