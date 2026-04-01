<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

class Entity_Log_Exception extends Entity
{
	public int|string $exceptionId;
	public int|string $requestId	= 0;
	public int $status				= Model_Log_Exception::STATUS_NONE;
	public string $type;
	public string $message;
	public string $code;
	public string $file;
	public int $line;
	public string $trace;
	public ?string $previous		= NULL;
	public ?string $sqlCode			= NULL;
	public ?string $subject			= NULL;
	public ?string $resource		= NULL;
	public string $env;
	public ?string $request			= NULL;
	public ?string $session			= NULL;
	public int $createdAt;
	public int $modifiedAt;

	protected static array $mandatoryFields	= [
		'type',
		'message',
		'file',
		'line',
		'trace',
		'env',
	];
}
