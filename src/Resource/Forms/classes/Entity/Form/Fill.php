<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Fill extends Entity
{
	public int|string $fillId;
	public int|string $formId;
	public int $status				= Model_Form_Fill::STATUS_NEW;
	public string $email			= '';
	public ?string $data			= NULL;
	public ?string $referer			= NULL;
	public ?string $agent			= NULL;
	public int $createdAt			= 0;
	public int $modifiedAt			= 0;

	public array $transfers			= [];
	public ?Entity_Form $form		= NULL;
}
