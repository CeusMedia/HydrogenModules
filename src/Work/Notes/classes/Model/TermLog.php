<?php

use CeusMedia\HydrogenFramework\Model;

class Model_TermLog extends Model
{
	protected string $name		= 'term_log';

	protected array $columns	= array(
		'termLogId',
		'userId',
		'count',
		'content',
		'createdAt',
		'modifiedAt'
	);

	protected string $primaryKey	= 'termLogId';

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
