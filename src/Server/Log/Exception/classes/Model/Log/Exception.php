<?php
/**
 *	Exception Log Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Exception Log Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Log_Exception extends Model
{
	public const STATUS_NONE		= 0;
	public const STATUS_HANDLED		= 1;
	public const STATUS_MARKED		= 2;

	protected string $name			= 'log_exceptions';

	protected array $columns		= [
		'exceptionId',
		'status',
		'type',
		'message',
		'code',
		'file',
		'line',
		'trace',
		'previous',
		'sqlCode',
		'subject',
		'resource',
		'env',
		'request',
		'session',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'exceptionId';

	protected array $indices		= [
		'status',
		'type',
		'code',
		'file',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
