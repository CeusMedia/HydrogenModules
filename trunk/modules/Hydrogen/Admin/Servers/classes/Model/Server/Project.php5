<?php
/**
 *	Model to relate projects onto servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	Model to relate projects onto servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Server_Project extends CMF_Hydrogen_Model {

	protected $name		= 'server_projects';
	protected $columns	= array(
		'serverProjectId',
		'serverId',
		'projectId',
		'projectVersionId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'serverProjectId';
	protected $indices		= array(
		'serverId',
		'projectId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
