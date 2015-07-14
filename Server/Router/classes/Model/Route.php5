<?php
/**
 *	Data Model of Route.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			...
 *	@version		...
 */
/**
 *	Data Model of Route.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			...
 *	@version		...
 */
class Model_Route extends CMF_Hydrogen_Model {

	protected $name		= 'routes';
	protected $columns	= array(
		"routeId",
		"status",
		"regex",
		"code",
		"source",
		"target",
		"title",
		"createdAt",
	);
	protected $primaryKey	= 'routeId';
	protected $indices		= array(
		"status",
		"regex",
		"code",
		"source",
		"target",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
