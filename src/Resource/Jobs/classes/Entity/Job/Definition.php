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

	public array $schedules				= [];

	protected static array $mandatoryFields	= [
		'identifier',
		'className',
		'methodName',
		'createdAt',
		'modifiedAt',
	];

	/**
	 *	Applies preset values dynamically created on manual construction.
	 *	@param		array		$array		Data array to work on
	 *	@return		array
	 */
	protected static function presetDynamicValues( array $array ): array
	{
		$array['createdAt']		= time();
		$array['modifiedAt']	= time();
		return $array;
	}
}