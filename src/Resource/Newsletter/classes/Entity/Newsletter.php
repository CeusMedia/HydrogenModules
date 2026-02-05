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
	public string $description					= '';
	public string $heading						= '';
	public int $generatePlain					= 0;
	public string $trackingCode					= '';
	public string $plain						= '';
	public string $html							= '';
//	public string $attachments;
	public int $createdAt						= 0;
	public int $modifiedAt						= 0;
	public int $sentAt							= 0;
}