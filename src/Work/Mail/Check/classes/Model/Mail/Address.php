<?php
/**
 *	Data model of addresses to check.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of addresses to check.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Address extends Model
{
	protected string $name		= 'mail_addresses';

	protected array $columns	= array(
		"mailAddressId",
		"mailGroupId",
		"status",
		"address",
		"data",
		"createdAt",
		"checkedAt",
	);

	protected string $primaryKey	= 'mailAddressId';

	protected array $indices		= array(
		"mailGroupId",
		"status",
		"address",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
