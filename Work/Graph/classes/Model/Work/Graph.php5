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
class Model_Work_Graph extends Model
{
	protected $name		= 'graphs';

	protected $columns	= array(
		"graphId",
		"type",
		"rankdir",
		"title",
		"description",
		"nodeShape",
		"nodeStyle",
		"nodeColor",
		"nodeFillcolor",
		"nodeWidth",
		"nodeHeight",
		"nodeFontsize",
		"nodeFontcolor",
		"edgeArrowhead",
		"edgeArrowsize",
		"edgeColor",
		"edgeFontcolor",
		"edgeFontsize",
		"dot",
		"image",
		"createdAt",
		"modifiedAt",
		"renderedAt",
	);

	protected $primaryKey	= 'graphId';

	protected $indices		= array(
		"type",
		"rankdir",
		"title",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
