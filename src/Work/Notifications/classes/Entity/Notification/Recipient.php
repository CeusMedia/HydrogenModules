<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Notification_Recipient extends Entity
{
	public int|string $notificationRecipientId;
	public int|string $notificationMessageId;
	public int|string $userId;
	public int $status						= Model_Notification_Recipient::STATUS_NEW;
	public int $createdAt;
	public int $modifiedAt					= 0;

	public static array $mandatoryFields	= [
		'notificationMessageId',
		'userId',
		'createdAt',
	];
}