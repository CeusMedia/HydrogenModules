<?php
/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Uberlog Client Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Log_Client extends CMF_Hydrogen_Model {

	protected $name			= 'log_clients';
	protected $columns		= array(
		'logClientId',
		'title',
		'createdAt',
		'loggedAt',
	);
	protected $primaryKey	= 'logClientId';
	protected $indices		= array(
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>