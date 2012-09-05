<?php
/**
 *	Uberlog Record Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Uberlog Record Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Types:
 *	-3		error
 *	-2		warning
 *	-1		notice
 *	 0		info
 *	 1		success
 *	 2		done
 */
class Model_Log_Record extends CMF_Hydrogen_Model {

	protected $name			= 'log_records';
	protected $columns		= array(
		'recordId',
		'logCategoryId',
		'logClientId',
		'logHostId',
		'logUserAgentId',
		'type',
		'code',
		'category',
		'source',
		'line',
		'message',
		'client',
		'timestamp'
	);
	protected $primaryKey	= 'recordId';
	protected $indices		= array(
		'logCategoryId',
		'logClientId',
		'logHostId',
		'logUserAgentId',
		'type',
		'category',
		'client',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>