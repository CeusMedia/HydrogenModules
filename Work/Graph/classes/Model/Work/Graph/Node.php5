<?php
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Work_Graph_Node extends Model
{
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
