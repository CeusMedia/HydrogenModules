<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Newsletter_Reader extends Entity
{
	public int|string $newsletterReaderId	= 0;
	public int $status						= Model_Newsletter_Reader::STATUS_REGISTERED;
	public string $email;
	public int $gender						= Model_Newsletter_Reader::GENDER_FEMALE;
	public ?string $prefix					= NULL;
	public string $firstname;
	public string $surname;
	public ?string $institution				= NULL;
	public int $tester						= 0;
	public int $registeredAt				= 0;

	public ?Entity_Address $address			= NULL;
	public int $validUntil					= 0;

	/** @var array<Entity_Newsletter_Group> $groups */
	public array $groups					= [];
}