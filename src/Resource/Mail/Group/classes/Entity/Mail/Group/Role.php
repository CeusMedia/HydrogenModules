<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group_Role extends Entity
{
	public int|string $mailGroupRoleId;
	public int $status;
	public int $rank;
	public string $title;
	public int $read;
	public int $write;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'status',
		'rank',
		'title',
		'read',
		'write',
	];
}