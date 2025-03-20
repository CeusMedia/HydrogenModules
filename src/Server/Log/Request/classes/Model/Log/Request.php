<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Model;
use CeusMedia\HydrogenFramework\Model\Database\Table as DatabaseModel;

class Model_Log_Request extends DatabaseModel
{
	protected string $table			= 'log_requests';
	protected string $primaryKey	= 'requestId';
	protected array $columns		= [
		'requestId',
		'url',
		'method',
		'path',
		'request',
		'session',
		'cookie',
		'headers',
		'timestamp',
	];
	protected array $indices		= [
		'url',
		'method',
		'path',
		'timestamp',
	];
	protected int $fetchMode		= PDO::FETCH_OBJ;
}