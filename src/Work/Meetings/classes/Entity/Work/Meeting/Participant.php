<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Work_Meeting_Participant extends Entity
{
	public int|string $meetingParticipantId	= 0;
	public int|string $meetingId			= 0;
	public int|string $userId				= 0;
	public int $type						= Model_Work_Meeting_Participant::TYPE_UNSPECIFIED;
	public int $timestamp;

	/** @var Entity_Work_Meeting_Participant[] $participants */
	public array $participants				= [];

	/** @var Entity_User[] $user */
	public ?Entity_User $user				= NULL;

	protected static array $mandatoryFields	= [
		'meetingId',
		'userId',
	];

	/**
	 *	@param		array		$array
	 *	@return		array
	 */
	protected static function presetStaticValues( array $array ): array
	{
		$array['timestamp']	= time();
		return $array;
	}
}
