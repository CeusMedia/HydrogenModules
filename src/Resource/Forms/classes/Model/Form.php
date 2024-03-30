<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form extends Model
{
	public const TYPE_NORMAL		= 0;
	public const TYPE_CONFIRM		= 1;

	public const STATUS_DISABLED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVATED	= 1;

	protected array $columns		= [
		'formId',
		'customerMailId',
		'managerMailId',
		'type',
		'status',
		'title',
		'receivers',
		'content',
		'timestamp',
	];

	protected array $indices		= [
		'customerMailId',
		'managerMailId',
		'status',
	];

	protected string $primaryKey	= 'formId';

	protected string $name			= 'forms';

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
