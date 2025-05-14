<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Shop_Customer extends Entity
{
	public int|string $customerId;

	public ?Entity_Address $addressBilling	= NULL;
	public ?Entity_Address $addressDelivery	= NULL;
}