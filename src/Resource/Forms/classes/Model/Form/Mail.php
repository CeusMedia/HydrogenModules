<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Form_Mail extends Model
{
	public const ROLE_TYPE_NONE				= 0;
	public const ROLE_TYPE_ALL				= 1;
	public const ROLE_TYPE_CUSTOMER_ALL		= 11;
	public const ROLE_TYPE_CUSTOMER_RESULT	= 12;
	public const ROLE_TYPE_CUSTOMER_REACT	= 13;
	public const ROLE_TYPE_LEADER_ALL		= 21;
	public const ROLE_TYPE_LEADER_RESULT	= 22;
	public const ROLE_TYPE_LEADER_REACT		= 23;
	public const ROLE_TYPE_MANAGER_ALL		= 31;
	public const ROLE_TYPE_MANAGER_RESULT	= 32;
	public const ROLE_TYPE_MANAGER_REACT	= 33;

	public const ROLE_TYPES					= [
		self::ROLE_TYPE_NONE,
		self::ROLE_TYPE_ALL,
		self::ROLE_TYPE_CUSTOMER_ALL,
		self::ROLE_TYPE_CUSTOMER_RESULT,
		self::ROLE_TYPE_CUSTOMER_REACT,
		self::ROLE_TYPE_LEADER_ALL,
		self::ROLE_TYPE_LEADER_RESULT,
		self::ROLE_TYPE_LEADER_REACT,
		self::ROLE_TYPE_MANAGER_ALL,
		self::ROLE_TYPE_MANAGER_RESULT,
		self::ROLE_TYPE_MANAGER_REACT,
	];

	public const FORMAT_TEXT				= 1;
	public const FORMAT_HTML				= 2;

	public const FORMATS					= [
		self::FORMAT_TEXT,
		self::FORMAT_HTML,
	];

	protected array $columns				= [
		'mailId',
		'roleType',
		'identifier',
		'format',
		'subject',
		'title',
		'content',
	];

	protected array $indices				= [
		'roleType',
		'identifier',
		'format',
	];

	protected string $primaryKey			= 'mailId';

	protected string $name					= 'form_mails';

	protected int $fetchMode			= PDO::FETCH_CLASS;

	protected ?string $className		= Entity_Form_Mail::class;
}
