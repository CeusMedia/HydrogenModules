<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Project extends Entity
{
	public int|string $projectId;
	public int|string $creatorId;
	public int|string $parentId;
	public int $status				= 0;
	public string $priority;
	public ?string $url				= NULL;
	public string $title;
	public string $description;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;

	public bool $isDefault			= FALSE;
}