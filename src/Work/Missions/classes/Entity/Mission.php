<?php

/**
 * @property ?Entity_User $creator
 * @property ?Entity_User $modifier
 * @property ?Entity_User $worker
 * @property ?Entity_Project $project
 * @property Entity_Mission_Version[] $versions
 */
class Entity_Mission
{
	public string $missionId;
	public int|string $creatorId		= 0;
	public int|string $modifierId		= 0;
	public int|string $workerId			= 0;
	public int|string $projectId		= 0;
	public int $type					= Model_Mission::TYPE_TASK;
	public int $priority				= Model_Mission::PRIORITY_NORMAL;
	public int $status					= Model_Mission::STATUS_NEW;
	public string $dayStart;
	public string $dayEnd;
	public ?string $timeStart			= NULL;
	public ?string $timeEnd				= NULL;
	public string $minutesProjected;
	public string $minutesRequired;
	public string $title;
	public string $content;
	public ?string $location			= NULL;
	public string $format;
	public ?string $reference			= NULL;
	public string $createdAt;
	public string $modifiedAt;
}