<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Payment_Backend extends Entity
{
	public bool $active				= TRUE;
	public string $backend;
	public string $key;
	public string $title;
	public string $path;
	public int|float $priority		= 5;
	public string $icon				= '';
	public array $countries			= [];
	public bool $feeExclusive		= FALSE;
	public string $feeFormula		= '';
	public ?string $description		= NULL;
}
