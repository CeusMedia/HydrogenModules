<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Template extends Entity
{
	public int|string $newsletterTemplateId		= 0;
	public int|string $creatorId		= 0;
	public ?string $themeId				= NULL;
	public string $version				= '1';
	public int $status					= Model_Newsletter_Template::STATUS_WORK;
	public string $title;
	public ?string $senderAddress	= NULL;
	public ?string $senderName		= NULL;
	public string $plain			= '';
	public string $html				= '';
	public string $style			= '';
	public array|string $styles			= '';
	public string $imprint			= '';
	public ?string $authorName		= NULL;
	public ?string $authorEmail		= NULL;
	public ?string $authorUrl		= NULL;
	public ?string $authorCompany	= NULL;
	public ?string $license			= NULL;
	public ?string $licenseUrl		= NULL;
	public ?string $description		= NULL;
	public int $createdAt			= 0;
	public int $modifiedAt			= 0;
}
