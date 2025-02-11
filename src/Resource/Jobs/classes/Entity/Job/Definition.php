<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Job_Definition extends Entity
{
	public int|string $jobDefinitionId;
	public int $mode					= Model_Job_Definition::MODE_UNDEFINED;
	public int $status					= Model_Job_Definition::STATUS_DISABLED;
	public string $identifier;
	public string $className;
	public string $methodName;
	public ?string $arguments			= NULL;
	public int $runs					= 0;
	public int $fails					= 0;
	public int $createdAt;
	public int $modifiedAt;
	public int $lastRunAt				= 0;

	protected static array $mandatoryFields	= [
		'identifier',
		'className',
		'methodName',
		'createdAt',
		'modifiedAt',
	];

	protected static function presetDynamicValues( array & $array ): void
	{
		$array['createdAt']		= time();
		$array['modifiedAt']	= time();
	}
}