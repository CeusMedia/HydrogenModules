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
	protected $name		= 'mail_addresses';

	protected $columns	= array(
		"mailAddressId",
		"mailGroupId",
		"status",
		"address",
		"data",
		"createdAt",
		"checkedAt",
	);

	protected $primaryKey	= 'mailAddressId';

	protected $indices		= array(
		"mailGroupId",
		"status",
		"address",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
