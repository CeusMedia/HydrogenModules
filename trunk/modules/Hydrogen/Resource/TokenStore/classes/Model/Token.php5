<?php
/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Token Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Token extends CMF_Hydrogen_Model {

	protected $name			= 'tokens';
	protected $columns		= array(
		'tokenId',
		'token',
		'ip',
		'timestamp',
	);
	protected $primaryKey	= 'tokenId';
	protected $indices		= array(
		'token',
		'ip'
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>