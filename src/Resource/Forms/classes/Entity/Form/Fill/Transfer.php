<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Fill_Transfer extends Entity
{
	public int|string $formFillTransferId;
	public int|string $formId;
	public int|string $formTransferRuleId;
	public int|string $formTransferTargetId;
	public int|string $fillId;
	public int $status					= Model_Form_Fill_Transfer::STATUS_UNKNOWN;
	public string $data;
	public ?string $message				= NULL;
	public ?string $trace				= NULL;
	public int $createdAt				= 0;
}