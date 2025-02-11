<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mission_Change extends Entity
{
	public int|string $missionChangeId;
	public int|string $missionId;
	public int|string $userId;
	public int $type;
	public string $data;
	public string $timestamp;
}