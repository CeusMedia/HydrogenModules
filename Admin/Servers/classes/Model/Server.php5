<?php
/**
 *	General server model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	General server model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class Model_Server extends CMF_Hydrogen_Model {

	protected $name		= 'servers';
	protected $columns	= array(
		'serverId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'serverId';
	protected $indices		= array(
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
