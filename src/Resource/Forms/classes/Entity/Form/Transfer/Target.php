<?php
declare(strict_types = 1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Transfer_Target extends Entity
{
	public int|string $formTransferTargetId;
	public int $status			= Model_Form_Transfer_Target::STATUS_DISABLED;
	public string $title;
	public ?string $className	= NULL;
	public ?string $baseUrl		= NULL;
	public ?string $apiKey		= NULL;
	public int $createdAt		= 0;
	public int $modifiedAt		= 0;


	public int|array $rules		= 0;
	public int|array $transfers	= 0;
	public int|array $fails		= 0;
	public int $usedAt			= 0;

}