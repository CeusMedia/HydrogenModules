<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Work_Meeting_Participant extends Entity
{
	public int|string $meetingParticipantId	= 0;
	public int|string $meetingId			= 0;
	public int|string $userId				= 0;
	public int $role						= Model_Work_Meeting_Participant::ROLE_UNSPECIFIED;
	public int $timestamp;

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