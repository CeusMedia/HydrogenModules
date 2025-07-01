<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group_Message extends Entity
{
	public int|string $mailGroupMessageId;
	public int|string $mailGroupId;
	public int|string $mailGroupMemberId	= 0;
	public int $status						= Model_Mail_Group_Message::STATUS_NEW;
	public int $parentId					= 0;
	public string $messageId;
	public string $raw;
	public string $object;
	public int $createdAt;
	public int $modifiedAt					= 0;

	protected static array $mandatoryFields	= [
		'mailGroupId',
		'mailGroupMemberId',
		'status',
		'parentId',
		'messageId',
	];
}