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
	protected string $name			= 'mail_address_checks';

	protected array $columns		= [
		"mailAddressCheckId",
		"mailAddressId",
		"status",
		"error",
		"code",
		"message",
		"createdAt",
	];

	protected string $primaryKey	= 'mailAddressCheckId';

	protected array $indices		= [
		"mailAddressId",
		"status",
		"error",
		"code",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
