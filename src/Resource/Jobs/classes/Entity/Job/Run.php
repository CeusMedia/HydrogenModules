<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Job_Run extends Entity
{
	public int|string $jobRunId;
	public int|string $jobDefinitionId;
	public int|string $jobScheduleId	= 0;
	public int $processId				= 0;
	public int $type					= Model_Job_Run::TYPE_MANUALLY;
	public int $status					= Model_Job_Run::STATUS_PREPARED;
	public int $archived				= Model_Job_Run::ARCHIVED_NO;
	public int $reportMode				= Model_Job_Run::REPORT_MODE_NEVER;
	public int $reportChannel			= Model_Job_Run::REPORT_CHANNEL_NONE;
	public ?string $reportReceivers		= NULL;
	public ?string $arguments			= NULL;
	public string $title;
	public ?string $message				= NULL;
	public int $createdAt;
	public int $modifiedAt;
	public int $ranAt					= 0;
	public int $finishedAt				= 0;

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