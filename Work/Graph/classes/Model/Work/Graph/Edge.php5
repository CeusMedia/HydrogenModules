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
class Model_Work_Graph_Edge extends CMF_Hydrogen_Model {

	protected $name		= 'graph_edges';
	protected $columns	= array(
		"edgeId",
		"graphId",
		"fromNodeId",
		"toNodeId",
		"label",
		"arrowhead",
		"arrowsize",
		"color",
		"fontcolor",
		"fontsize",
	);
	protected $primaryKey	= 'edgeId';
	protected $indices		= array(
		"graphId",
		"fromNodeId",
		"toNodeId",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
