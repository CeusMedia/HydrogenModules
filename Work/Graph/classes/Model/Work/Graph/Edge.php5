<?php
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Work_Graph_Edge extends Model
{
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
