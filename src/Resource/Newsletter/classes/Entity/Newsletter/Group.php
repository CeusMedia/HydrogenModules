<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Group extends Entity
{
	public int|string $newsletterGroupId	= 0;
	public int|string $creatorId			= 0;
	public int $status						= Model_Newsletter_Group::STATUS_NEW;
	public int $type						= Model_Newsletter_Group::TYPE_DEFAULT;
	public string $title					= '';
	public int $createdAt					= 0;
	public ?int $modifiedAt					= NULL;

	public bool $isChecked					= FALSE;
}