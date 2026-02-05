<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Template extends Entity
{
	public int|string $creatorId	= 0;
	public int $status				= Model_Newsletter_Template::STATUS_WORK;
	public string $title			= '';

}