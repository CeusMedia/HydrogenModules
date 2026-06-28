<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter extends Entity
{
	public int|string $newsletterId				= 0;
	public int|string $newsletterTemplateId		= 0;
	public int|string $creatorId				= 0;
	public int $status							= Model_Newsletter::STATUS_NEW;
	public string $senderAddress				= '';
	public string $senderName					= '';
	public string $title						= '';
	public string $subject						= '';
	public ?string $description					= NULL;
	public string $heading						= '';
	public int $generatePlain					= 0;
	public ?string $trackingCode				= NULL;
	public ?string $plain						= NULL;
	public ?string $html						= NULL;
//	public string $attachments;
	public int $createdAt						= 0;
	public ?int $modifiedAt						= NULL;
	public ?int $enqueuedAt						= NULL;
	public ?int $sentAt							= NULL;

	protected static array $mandatoryFields		= [
		'newsletterTemplateId',
		'creatorId',
		'senderAddress',
		'title',
		'subject',
		'heading',
		'createdAt',
	];
}
