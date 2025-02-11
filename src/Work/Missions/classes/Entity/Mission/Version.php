<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mission_Version extends Entity
{
	public int|string $missionVersionId;
	public int|string $missionId;
	public int|string $userId;
	public int $version;
	public string $content;
	public string $timestamp;
}