<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Role_Right extends Entity
{
	public int|string $roleRightId;
	public int|string $roleId;
	public string $controller;
	public string $action;
	public string $timestamp;
}