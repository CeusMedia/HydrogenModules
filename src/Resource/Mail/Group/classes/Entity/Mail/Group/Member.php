<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group_Member extends Entity
{
	public int|string $mailGroupMemberId;
	public int|string $mailGroupId;
	public int|string $roleId;
	public int $status						= Model_Mail_Group_Member::STATUS_REGISTERED;
	public string $address;
	public string $title;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'mailGroupId',
		'roleId',
		'status',
		'address',
		'title',
	];
}