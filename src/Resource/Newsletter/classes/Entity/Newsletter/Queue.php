<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Queue extends Entity
{
	public int|string $newsletterQueueId	= 0;
	public int|string $newsletterId			= 0;
	public int|string $creatorId			= 0;
	public int $status						= Model_Newsletter_Queue::STATUS_NEW;
	public int $createdAt					= 0;
	public int $modifiedAt					= 0;
}