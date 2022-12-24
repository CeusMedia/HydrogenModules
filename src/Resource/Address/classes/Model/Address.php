<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Address extends Model
{
	const TYPE_LOCATION		= 1;
	const TYPE_BILLING		= 2;
	const TYPE_DELIVERY		= 4;

	protected string $name			= 'addresses';

	protected array $columns		= [
		'addressId',
		'relationId',
		'relationType',
		'type',
		'country',
		'state',
		'region',
		'city',
		'postcode',
		'street',
		'latitude',
		'longitude',
		'phone',
		'email',
		'institution',
		'firstname',
		'surname',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'addressId';

	protected array $indices		= [
		'relationId',
		'relationType',
		'type',
		'latitude',
		'longitude',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
