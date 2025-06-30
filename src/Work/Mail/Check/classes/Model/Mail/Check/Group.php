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
class Model_Mail_Check_Group extends Model
{
	protected string $name			= 'mail_check_groups';

	protected array $columns		= [
		"mailCheckGroupId",
		"title",
		"columns",
		"mailColumn",
		"createdAt",
	];

	protected string $primaryKey	= 'mailCheckGroupId';

	protected array $indices		= [
		"title",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
