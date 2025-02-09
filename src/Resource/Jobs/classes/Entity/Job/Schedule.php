<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Job_Schedule extends Entity
{
	public int|string $jobScheduleId;
	public int|string $jobDefinitionId;
	public int $type					= Model_Job_Schedule::TYPE_UNKNOWN;
	public int $status					= Model_Job_Schedule::STATUS_DISABLED;
	public int $reportMode				= Model_Job_Schedule::REPORT_MODE_NEVER;
	public int $reportChannel			= Model_Job_Schedule::REPORT_CHANNEL_NONE;
	public ?string $reportReceivers		= NULL;
	public ?string $expression			= NULL;
	public ?string $arguments			= NULL;
	public string $title;
	public int $createdAt;
	public int $modifiedAt;
	public int $lastRunAt				= 0;

	protected static array $mandatoryFields	= [
		'jobDefinitionId',
		'title',
		'createdAt',
		'modifiedAt',
	];

	protected static function presetDynamicValues( array & $array ): void
	{
		$array['createdAt']		= time();
		$array['modifiedAt']	= time();
	}
}