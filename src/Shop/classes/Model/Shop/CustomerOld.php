<?php
/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */
class Model_Shop_CustomerOld extends Model
{
	protected string $name			= 'shop_customers_old';

	protected array $columns		= [
		'customerId',
		'firstname',
		'lastname',
		'country',
		'region',
		'city',
		'postcode',
		'address',
		'phone',
		'email',
		'password',
		'longitude',
		'latitude',
		'institution',
		'alternative',
		'billing_institution',
		'billing_firstname',
		'billing_lastname',
		'billing_tnr',
		'billing_country',
		'billing_city',
		'billing_postcode',
		'billing_address',
		'billing_phone',
		'billing_email',
	];

	protected string $primaryKey	= 'customerId';

	protected array $indices		= [
		'lastname',
		'country',
		'email',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
