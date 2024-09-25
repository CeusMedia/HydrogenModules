<?php

class Entity_Project
{
	public int|string $projectId;
	public int|string $creatorId;
	public int|string $parentId;
	public int $status;
	public string $priority;
	public ?string $url				= NULL;
	public string $title;
	public string $description;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;
}