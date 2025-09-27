<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Work_Meeting extends Entity
{
	public int|string $meetingId	= 0;
	public int|string $creatorId	= 0;
	public int $status				= Model_Work_Meeting::STATUS_NEW;
	public string $dateStart;
	public string $dateEnd;
	public string $location;
	public string $title;
	public string $content;
	public string|NULL $link		= NULL;
	public int $createdAt;
	public int|NULL $modifiedAt		= NULL;

	/** @var Entity_Work_Meeting_Participant[] $participants */
	public array $participants		= [];

	protected static array $mandatoryFields	= [
		'startsAt',
		'minutes',
		'location',
		'title',
	];

	/**
	 *	@param		array		$array
	 *	@return		array
	 */
	protected static function presetStaticValues( array $array ): array
	{
		$array['createdAt']	= time();
		return $array;
	}
}