<?php
/**
 *	Job Definition Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Job Definition Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Job_Definition extends Model
{
	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;
	public const STATUS_DEPRECATED	= 2;

	public const MODE_UNDEFINED		= 0;
	public const MODE_SINGLE		= 1;
	public const MODE_MULTIPLE		= 2;
	public const MODE_EXCLUSIVE		= 3;

	protected string $name			= 'job_definitions';

	protected array $columns		= [
		'jobDefinitionId',
		'mode',
		'status',
		'identifier',
		'className',
		'methodName',
		'arguments',
		'runs',
		'fails',
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	];

	protected string $primaryKey	= 'jobDefinitionId';

	protected array $indices		= [
		'mode',
		'status',
		'identifier',
		'className',
		'methodName',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Job_Definition::class;
}
