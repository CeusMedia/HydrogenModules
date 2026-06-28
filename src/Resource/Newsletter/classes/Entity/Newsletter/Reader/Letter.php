<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Reader_Letter extends Entity
{
	public int|string $newsletterReaderLetterId	= 0;
	public int|string $newsletterReaderId		= 0;
	public int|string $newsletterQueueId		= 0;
	public int|string $newsletterId				= 0;
	public int|string $mailId					= 0;
	public int $status							= Model_Newsletter_Reader_Letter::STATUS_NEW;
	public int $enqueuedAt						= 0;
	public ?int $sentAt							= NULL;
	public ?int $openedAt						= NULL;

	public ?Entity_Newsletter_Reader $reader	= NULL;
}
