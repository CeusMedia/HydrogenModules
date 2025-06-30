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
class Model_Mail_Check_Address extends Model
{
	protected string $name			= 'mail_check_addresses';

	protected array $columns		= [
		'mailCheckAddressId',
		'mailCheckGroupId',
		'status',
		'address',
		'data',
		'createdAt',
		'checkedAt',
	];

	protected string $primaryKey	= 'mailCheckAddressId';

	protected array $indices		= [
		'mailCheckGroupId',
		'status',
		'address',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
