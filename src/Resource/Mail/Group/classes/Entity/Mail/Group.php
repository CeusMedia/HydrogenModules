<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group extends Entity
{
	public int|string $mailGroupId;
	public int|string $mailGroupServerId;
	public int|string $defaultRoleId;
	public int|string $managerId;
	public int $type						= Model_Mail_Group::TYPE_AUTOJOIN;
	public int $visibility					= Model_Mail_Group::VISIBILITY_PUBLIC;
	public int $status						= Model_Mail_Group::STATUS_NEW;
	public string $title;
	public string $address;
	public string $password;
	public string $bounce;
	public string $subtitle;
	public string $description;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'mailGroupServerId',
		'defaultRoleId',
		'managerId',
		'type',
		'visibility',
		'status',
		'title',
		'address',
	];
}