<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form extends Model
{
	const TYPE_NORMAL		= 0;
	const TYPE_CONFIRM		= 1;

	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVATED	= 1;

	protected array $columns		= array(
		'formId',
		'customerMailId',
		'managerMailId',
		'type',
		'status',
		'title',
		'receivers',
		'content',
		'timestamp',
	);

	protected array $indices		= array(
		'customerMailId',
		'managerMailId',
		'status',
	);

	protected string $primaryKey	= 'formId';

	protected string $name			= 'forms';

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
