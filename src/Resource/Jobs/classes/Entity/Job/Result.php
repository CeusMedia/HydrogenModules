<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Job_Result extends Entity
{
	public const STATUS_UNKNOWN 	= 0;
	public const STATUS_SUCCESS 	= 1;
	public const STATUS_PARTIAL 	= 2;
	public const STATUS_FAILURE 	= 3;

	public int $status				= self::STATUS_UNKNOWN;
	public int $count				= 0;
	public array|object|NULL $data	= NULL;
}