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
class Model_Mail_Group_Server extends CMF_Hydrogen_Model {

	protected $name		= 'mail_group_servers';
	protected $columns	= array(
		"mailGroupServerId",
		"status",
		"host",
		"port",
		"title",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'mailGroupServerId';
	protected $indices		= array(
		"status",
		"host",
		"port",
		"title",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
