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
	protected string $name			= 'mail_groups';

	protected array $columns		= [
		"mailGroupId",
		"title",
		"columns",
		"mailColumn",
		"createdAt",
	];

	protected string $primaryKey	= 'mailGroupId';

	protected array $indices		= [
		"title",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
