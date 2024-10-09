<?php
declare(strict_types=1);

class Entity_Address
{
	public int|string $addressId;
	public int|string $relationId;
	public int|string $relationType;
	public int $type				= Model_Address::TYPE_LOCATION;
	public ?string $country			= NULL;
	public ?string $state			= NULL;
	public ?string $region			= NULL;
	public ?string $city			= NULL;
	public ?string $postcode		= NULL;
	public ?string $street			= NULL;
	public ?string $latitude		= NULL;
	public ?string $longitude		= NULL;
	public ?string $phone			= NULL;
	public ?string $email			= NULL;
	public ?string $institution		= NULL;
	public ?string $firstname		= NULL;
	public ?string $surname			= NULL;
	public string $createdAt;
	public ?string $modifiedAt		= NULL;
}
