<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Group_Right extends Entity
{
	public int|string $groupRightId;
	public int|string $groupId;
	public string $controller;
	public string $action;
	public string $timestamp;
}