<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Fill extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_CONFIRMED	= 1;
	const STATUS_HANDLED	= 2;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_CONFIRMED,
		self::STATUS_HANDLED,
	];

	protected $columns		= [
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

	protected $indices		= [
		'formId',
		'status',
		'email',
	];

	protected $primaryKey	= 'fillId';

	protected $name			= 'form_fills';

	protected $fetchMode	= PDO::FETCH_OBJ;
}
