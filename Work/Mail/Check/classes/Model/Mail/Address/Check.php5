<?php
/**
 *	Data model of address checks.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of address checks.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Address_Check extends Model
{
	protected $name		= 'mail_address_checks';

	protected $columns	= array(
		"mailAddressCheckId",
		"mailAddressId",
		"status",
		"error",
		"code",
		"message",
		"createdAt",
	);

	protected $primaryKey	= 'mailAddressCheckId';

	protected $indices		= array(
		"mailAddressId",
		"status",
		"error",
		"code",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
