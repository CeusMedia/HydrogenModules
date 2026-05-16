<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Fill extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_CONFIRMED	= 1;
	public const STATUS_HANDLED		= 2;
	public const STATUS_CUSTOMER	= 4;
	public const STATUS_BOOKING		= 8;
	public const STATUS_ORDER		= 16;
	public const STATUS_NEWSLETTER	= 32;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_CONFIRMED,
		self::STATUS_HANDLED,
		self::STATUS_CUSTOMER,
		self::STATUS_BOOKING,
		self::STATUS_ORDER,
		self::STATUS_NEWSLETTER,
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

	protected array $generated		= [
		'hasStatusConfirmed',
		'hasStatusHandled',
		'hasStatusCustomer',
		'hasStatusBooking',
		'hasStatusOrder',
		'hasStatusNewsletter',
	];
	
	protected string $primaryKey	= 'fillId';

	protected string $name			= 'form_fills';

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Form_Fill::class;
}
