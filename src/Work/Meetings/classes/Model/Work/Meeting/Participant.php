<?php
class Model_Work_Meeting_Participant extends CeusMedia\HydrogenFramework\Model\Database\Table
{
	public const ROLE_UNSPECIFIED	= 0;		//  Leiter
	public const ROLE_LEADER		= 1;		//  Leiter
	public const ROLE_SPEAKER		= 2;		//  Sprecher
	public const ROLE_CONTRIBUTOR	= 3;		//  Teilnehmer
	public const ROLE_LISTENER		= 4;		//  Zuhörer
	public const ROLE_OBSERVER		= 5;		//  Beobachter
	public const ROLE_BYSTANDER		= 6;		//  Beisteher
	public const ROLE_INFORMED		= 7;		//  Informierter

	public const ROLES				= [
		self::ROLE_UNSPECIFIED,
		self::ROLE_LEADER,
		self::ROLE_SPEAKER,
		self::ROLE_CONTRIBUTOR,
		self::ROLE_LISTENER,
		self::ROLE_OBSERVER,
		self::ROLE_BYSTANDER,
		self::ROLE_INFORMED,
	];

	protected string $name				= 'work_meeting_participants';
	protected array $columns			= [
		'workMeetingParticipantId',
		'workMeetingId',
		'userId',
		'role',
		'timestamp',
	];
	protected array $indexes			= [
		'workMeetingId',
		'userId',
		'role',
	];
	protected string $primaryKey		= 'workMeetingParticipantId';

	protected int $fetchMode			= PDO::FETCH_CLASS;
	protected ?string $fetchEntityClass	= Entity_Work_Meeting_Participant::class;

}