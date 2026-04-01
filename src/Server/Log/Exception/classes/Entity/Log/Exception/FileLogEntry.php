<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Log_Exception_FileLogEntry extends Entity
{
	public int|string $requestId	= 0;
	public string $class;
	public array $classParents		= [];
	public array $classInterfaces	= [];
	public array $env				= [];
	public int|string $timestamp;
	public ?Throwable $exception	= NULL;

	//  Resources for specific exceptions
	public ?string $subject			= NULL;
	public ?string $resource		= NULL;

	//  FALLBACK: Exception is not serializable
	public ?string $message			= NULL;
	public ?string $trace			= NULL;
	public ?string $previous		= NULL;
	public ?string $code			= NULL;
	public ?string $file			= NULL;
	public ?int $line				= NULL;
	public ?string $sqlState		= NULL;

	//  FALLBACK: Request log not available
	public string|object|NULL $request			= NULL;
	public string|object|NULL $session			= NULL;

	protected static array $mandatoryFields	= [
	];
}
