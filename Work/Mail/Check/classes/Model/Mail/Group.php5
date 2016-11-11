<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Mail_Group extends CMF_Hydrogen_Model {

	protected $name		= 'mail_groups';
	protected $columns	= array(
		"mailGroupId",
		"title",
		"columns",
		"mailColumn",
		"createdAt",
	);
	protected $primaryKey	= 'mailGroupId';
	protected $indices		= array(
		"title",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
