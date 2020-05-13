<?php
/**
 *	Job Definition Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
/**
 *	Job Definition Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class Model_Job_Definition extends CMF_Hydrogen_Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;
	const STATUS_DEPRECATED	= 2;

	const MODE_UNDEFINED	= 0;
	const MODE_SINGLE		= 1;
	const MODE_MULTIPLE		= 2;
	const MODE_EXCLUSIVE	= 3;

	protected $name			= 'job_definitions';
	protected $columns		= array(
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
	);
	protected $primaryKey	= 'jobDefinitionId';
	protected $indices		= array(
		'mode',
		'status',
		'identifier',
		'className',
		'methodName',
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
