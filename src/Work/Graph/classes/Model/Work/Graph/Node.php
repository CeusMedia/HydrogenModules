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
	protected string $name		= 'graph_nodes';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'nodeId';

	protected array $indices		= array(
		"graphId",
		"ID",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
