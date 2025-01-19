<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Group extends Entity
{
	public int|string $groupId;
	public int|string $accountId;
	public int|string $leaderId;
	public int|string $companyId;
	public int $status					= Model_Group::STATUS_NEW;
	public int $type					= 0;
	public string $title;
	public ?string $description;
	public ?string $email				= NULL;
	public string $createdAt;
	public ?string $modifiedAt			= NULL;

	/** @var Entity_User[] $users */
	public array $users					= [];
}