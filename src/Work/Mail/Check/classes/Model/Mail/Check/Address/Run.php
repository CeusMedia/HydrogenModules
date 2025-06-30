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
class Model_Mail_Check_Address_Run extends Model
{
	protected string $name			= 'mail_check_address_runs';

	protected array $columns		= [
		"mailCheckAddressRunId",
		"mailCheckAddressId",
		"status",
		"error",
		"code",
		"message",
		"createdAt",
	];

	protected string $primaryKey	= 'mailCheckAddressRunId';

	protected array $indices		= [
		"mailCheckAddressId",
		"status",
		"error",
		"code",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
