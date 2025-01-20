<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Form extends Entity
{
	public int|string $formId;
	public int|string $customerMailId	= 0;
	public int|string $managerMailId	= 0;
	public int $type					= Model_Form::TYPE_NORMAL;
	public int $status					= Model_Form::STATUS_NEW;
	public string $title				= '';
	public ?string $receivers			= NULL;
	public string $content				= '';
	public ?string $forwardOnSuccess	= NULL;
	public int $timestamp				= 0;

	public array $attachments			= [];
}
