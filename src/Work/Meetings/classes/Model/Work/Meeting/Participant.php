<?php
class Model_Work_Meeting_Participant extends CeusMedia\HydrogenFramework\Model\Database\Table
{
	public const TYPE_UNSPECIFIED	= 0;		//  ...
	public const TYPE_LEADER		= 1;		//  Leiter
	public const TYPE_SPEAKER		= 2;		//  Sprecher
	public const TYPE_CONTRIBUTOR	= 3;		//  Teilnehmer
	public const TYPE_LISTENER		= 4;		//  Zuhörer
	public const TYPE_OBSERVER		= 5;		//  Beobachter
	public const TYPE_BYSTANDER		= 6;		//  Beisteher
	public const TYPE_INFORMED		= 7;		//  Informierter

	public const TYPES				= [
		self::TYPE_UNSPECIFIED,
		self::TYPE_LEADER,
		self::TYPE_SPEAKER,
		self::TYPE_CONTRIBUTOR,
		self::TYPE_LISTENER,
		self::TYPE_OBSERVER,
		self::TYPE_BYSTANDER,
		self::TYPE_INFORMED,
	];

	protected string $name			= 'meeting_participants';
	protected array $columns		= [
		'meetingParticipantId',
		'meetingId',
		'userId',
		'type',
		'timestamp',
	];
	protected array $indices		= [
		'meetingId',
		'userId',
		'type',
	];
	protected string $primaryKey	= 'meetingParticipantId';

	protected int $fetchMode		= PDO::FETCH_CLASS;
	protected ?string $className	= Entity_Work_Meeting_Participant::class;

}
