<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Model;
use CeusMedia\HydrogenFramework\Model\Database\Table as DatabaseModel;

class Model_Log_Request extends DatabaseModel
{
	protected string $name			= 'log_requests';
	protected string $primaryKey	= 'requestId';
	protected array $columns		= [
		'requestId',
		'ip',
		'sessionId',
		'method',
		'url',
		'request',
		'session',
		'cookie',
		'headers',
		'responseCode',
		'responseTime',
		'responseType',
		'responseContent',
		'body',
		'timestamp',
	];
	protected array $indices		= [
		'ip',
		'sessionId',
		'method',
		'url',
		'responseCode',
		'responseType',
		'timestamp',
	];
	protected int $fetchMode		= PDO::FETCH_OBJ;
}
