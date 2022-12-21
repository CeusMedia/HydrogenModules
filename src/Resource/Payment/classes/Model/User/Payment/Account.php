<?php
/**
 *	User Payment Account Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2016 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Payment Account Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2016 Ceus Media
 */
class Model_User_Payment_Account extends Model
{
	protected string $name		= 'user_payment_accounts';

	protected array $columns	= array(
		'userPaymentAccountId',
		'userId',
		'paymentAccountId',
		'provider',
		'createdAt',
	);

	protected string $primaryKey	= 'userPaymentAccountId';

	protected array $indices		= array(
		'userId',
		'paymentAccountId',
		'provider',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
