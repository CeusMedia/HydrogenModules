<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Import_Source extends Entity
{
	public string $id;
	public string $type;
	public Entity_Import_Search $search;
}

