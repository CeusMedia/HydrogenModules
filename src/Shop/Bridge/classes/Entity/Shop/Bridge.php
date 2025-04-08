<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Bridge extends Entity
{
	public string $bridgeId;
	public string $title;
	public string $class;
	public string $frontendController;
	public string $frontendUriPath;
	public ?string $backendController		= NULL;
	public ?string $backendUriPath			= NULL;
	public string $articleTableName;
	public string $articleIdColumn;
	public string $createdAt;
}