<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Group_Relation extends Entity
{
	public int|string $groupRelationId;
	public int|string $groupId;
	public string $moduleId;
	public int|string $entityId;
	public string $timestamp;
}