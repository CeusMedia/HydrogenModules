<?php

use CeusMedia\HydrogenFramework\Model;

class Model_TermLog extends Model
{
	protected $name		= 'term_log';

	protected $columns	= array(
		'termLogId',
		'userId',
		'count',
		'content',
		'createdAt',
		'modifiedAt'
	);

	protected $primaryKey	= 'termLogId';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
