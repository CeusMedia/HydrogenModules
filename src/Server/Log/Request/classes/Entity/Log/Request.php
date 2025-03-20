<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Log_Request extends Entity
{
	public int|string $requestId;
	public string $url;
	public string $method;
	public string $ip;
	public string $userAgent;
	public string $referer;
	public string $request;
	public string $session;
	public string $cookie;
	public string $headers;
	public string $timestamp;

	protected static array $mandatoryFields	= [
		'url',
		'method',
		'ip',
		'userAgent',
		'referer',
		'request',
		'session',
		'cookie',
		'headers',
		'timestamp',
	];

}