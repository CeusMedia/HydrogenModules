<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Rule extends Entity
{
	public int|string $formRuleId;
	public int|string $formId;
	public int $type				= Model_Form_Rule::TYPE_CUSTOMER;
	public string $rules			= '';
	public ?string $mailAddresses	= NULL;
	public ?string $mailId			= NULL;
	public ?string $filePath		= NULL;
	public string $content			= '';
}

