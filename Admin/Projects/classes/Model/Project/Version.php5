<?php
/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Role.php 1760 2010-11-26 17:14:11Z christian.wuerker $
 */
class Model_Project_Version extends CMF_Hydrogen_Model {

	protected $name		= 'project_versions';
	protected $columns	= array(
		'projectVersionId',
		'projectId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectVersionId';
	protected $indices		= array(
		'projectId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
