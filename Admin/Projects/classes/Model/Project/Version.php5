<?php
/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Model_Project_Version extends CMF_Hydrogen_Model
{
	protected $name		= 'project_versions';

	protected $columns	= array(
		'projectVersionId',
		'projectId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'projectVersionId';

	protected $indices		= array(
		'projectId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
