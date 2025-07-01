<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group_Action extends Entity
{
	public int|string $mailGroupActionId;
	public int|string $mailGroupId;
	public int|string $mailGroupMemberId;
	public int $status						= Model_Mail_Group_Action::STATUS_REGISTERED;
	public string $uuid;
	public string $action;
	public string|NULL $message				= NULL;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'mailGroupId',
		'mailGroupMemberId',
		'status',
		'uuid',
		'action',
	];
}