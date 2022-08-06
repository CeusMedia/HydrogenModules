<?php
/**
 *	Exception Log Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Exception Log Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Roles.Model.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Ceus Media
 */
class Model_Log_Exception extends Model
{
	const STATUS_NONE		= 0;
	const STATUS_HANDLED	= 1;
	const STATUS_MARKED		= 2;

	protected $name		= 'log_exceptions';

	protected $columns	= [
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

	protected $primaryKey	= 'exceptionId';

	protected $indices		= [
		'status',
		'type',
		'code',
		'file',
		'createdAt',
		'modifiedAt',
	];

	protected $fetchMode	= PDO::FETCH_OBJ;
}
