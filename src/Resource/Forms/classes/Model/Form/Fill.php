<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Fill extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_CONFIRMED	= 1;
	public const STATUS_HANDLED		= 2;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_CONFIRMED,
		self::STATUS_HANDLED,
	];

	protected array $columns		= [
		'fillId',
		'formId',
		'status',
		'email',
		'data',
		'referer',
		'agent',
		'createdAt',
		'modifiedAt',
	];

	protected array $indices		= [
		'formId',
		'status',
		'email',
	];

	protected string $primaryKey	= 'fillId';

	protected string $name			= 'form_fills';

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
