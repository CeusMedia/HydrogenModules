<?php
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
class Model_Work_Graph_Node extends CMF_Hydrogen_Model {

	protected $name		= 'graph_nodes';
	protected $columns	= array(
		"nodeId",
		"graphId",
		"ID",
		"label",
		"description",
		"shape",
		"style",
		"color",
		"fillcolor",
		"width",
		"height",
		"fontsize",
		"fontcolor",
	);
	protected $primaryKey	= 'nodeId';
	protected $indices		= array(
		"graphId",
		"ID",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
