<?php
/**
 *	Project Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
/**
 *	Project Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
class Model_Project extends CMF_Hydrogen_Model {

	protected $name		= 'projects';
	protected $columns	= array(
		'projectId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectId';
	protected $indices		= array(
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
