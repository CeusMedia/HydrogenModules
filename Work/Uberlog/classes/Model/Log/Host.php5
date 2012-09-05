<?php
/**
 *	Uberlog Host Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Uberlog Host Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Log_Host extends CMF_Hydrogen_Model {

	protected $name			= 'log_hosts';
	protected $columns		= array(
		'logHostId',
		'title',
		'createdAt',
		'loggedAt',
	);
	protected $primaryKey	= 'logHostId';
	protected $indices		= array(
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>