<?php
/**
 *	Data model of address groups.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of address groups.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group extends Model
{
	protected $name		= 'mail_groups';

	protected $columns	= array(
		"mailGroupId",
		"title",
		"columns",
		"mailColumn",
		"createdAt",
	);

	protected $primaryKey	= 'mailGroupId';

	protected $indices		= array(
		"title",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
