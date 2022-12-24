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
class Model_Work_Graph_Edge extends Model
{
	protected string $name			= 'graph_edges';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'edgeId';

	protected array $indices		= [
		"graphId",
		"fromNodeId",
		"toNodeId",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
