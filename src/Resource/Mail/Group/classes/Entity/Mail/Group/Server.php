<?php

use CeusMedia\HydrogenFramework\Entity;

class Entity_Mail_Group_Server extends Entity
{
	public int|string $mailGroupServerId;
	public int $status;
	public string $imapHost;
	public int $imapPort;
	public string $smtpHost;
	public int $smtpPort;
	public string $title;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'status',
		'imapHost',
		'imapPort',
		'smtpHost',
		'smtpPort',
		'title',
	];
}