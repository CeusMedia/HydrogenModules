<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Log_Request extends Entity
{
	public int|string $requestId;
	public ?string $ip				= NULL;
	public ?string $sessionId		= NULL;
	public ?string $url				= NULL;
	public string $method;
	public ?string $userAgent		= NULL;
	public ?string $referer			= NULL;
	public ?string $request			= NULL;
	public ?string $session			= NULL;
	public ?string $cookie			= NULL;
	public ?string $headers			= NULL;
	public string $timestamp;

	protected static array $mandatoryFields	= [
#		'url',
		'method',
#		'ip',
#		'userAgent',
#		'referer',
#		'request',
#		'session',
#		'cookie',
#		'headers',
		'timestamp',
	];
}
