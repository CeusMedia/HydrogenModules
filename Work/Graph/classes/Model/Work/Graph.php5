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
class Model_Work_Graph extends CMF_Hydrogen_Model {

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
