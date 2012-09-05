<?php
/**
 *	Uberlog Category Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Uberlog Category Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Log_Category extends CMF_Hydrogen_Model {

	protected $name			= 'log_categories';
	protected $columns		= array(
		'logCategoryId',
		'title',
		'createdAt',
		'loggedAt',
	);
	protected $primaryKey	= 'logCategoryId';
	protected $indices		= array(
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>