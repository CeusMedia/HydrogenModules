<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Sitemap_Link extends Entity
{
	public string $location;
	public ?string $datetime			= NULL;
	public ?string $frequency			= NULL;
	public float|int|NULL $priority		= NULL;
}
