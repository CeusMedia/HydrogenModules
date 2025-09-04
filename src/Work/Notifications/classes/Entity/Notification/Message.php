<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Notification_Message extends Entity
{
	public int|string $notificationMessageId;
	public int|string $creatorId;
	public int $type				= Model_Notification_Message::TYPE_NONE;
	public int $priority			= Model_Notification_Message::PRIORITY_NORMAL;
	public int $status				= Model_Notification_Message::STATUS_NEW;
	public string $title;
	public string $content;
	public string|NULL $link		= NULL;
	public string $dayStart;
	public string|NULL $dayEnd		= NULL;
	public int $createdAt;
	public int $modifiedAt			= 0;

	public int $nrRecipients		= 0;
	public int $nrRecipientsSeen	= 0;

	public static array $mandatoryFields	= [
		'creatorId',
		'title',
		'content',
		'dateStart',
		'createdAt',
	];
}