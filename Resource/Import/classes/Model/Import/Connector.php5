<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021 Ceus Media
 */
/**
 *	User Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2021 Ceus Media
 */
class Model_Import_Connector extends CMF_Hydrogen_Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	protected $name		= 'import_connectors';

	protected $columns	= array(
		'importConnectorId',
		'creatorId',
		'status',
		'className',
		'type',
		'label',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'importConnectorId';

	protected $indices		= array(
		'creatorId',
		'status',
		'type',
		'className',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
