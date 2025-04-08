<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Payment_Backend extends Entity
{
	public string $backend;
	public string $key;
	public string $title;
	public string $path;
	public string $priority;
	public string $icon;
	public array $countries			= [];
	public bool $feeExclusive		= FALSE;
	public string $feeFormula;

	public ?string $label			= NULL;
	public ?string $description		= NULL;

}