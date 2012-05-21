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
class Model_Project_Server extends CMF_Hydrogen_Model {

	protected $name		= 'project_servers';
	protected $columns	= array(
		'projectServerId',
		'projectId',
		'serverId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectServerId';
	protected $indices		= array(
		'projectId',
		'serverId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
