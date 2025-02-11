<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form_Import_Rule extends Entity
{
	public int|string $formImportRuleId;
	public int|string $importConnectionId;
	public int|string $formId;
	public int $status				= Model_Form_Import_Rule::STATUS_NEW;
	public string $title;
	public string $searchCriteria	= '';
	public string $options			= '';
	public string $rules			= '';
	public ?string $moveTo			= NULL;
	public ?string $renameTo		= NULL;
	public int $createdAt			= 0;
	public int $modifiedAt			= 0;

	public ?Entity_Form $form		= NULL;
}