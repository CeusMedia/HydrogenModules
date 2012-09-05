<?php
/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Uberlog User Agent Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Log_UserAgent extends CMF_Hydrogen_Model {

	protected $name			= 'log_useragents';
	protected $columns		= array(
		'logUserAgentId',
		'title',
		'createdAt',
		'loggedAt',
	);
	protected $primaryKey	= 'logUserAgentId';
	protected $indices		= array(
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>