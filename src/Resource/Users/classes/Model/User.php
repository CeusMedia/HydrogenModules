<?php
/**
 *	User Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	User Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo			remove password column after old user password support decayed
 */
class Model_User extends Model
{
	public const GENDER_UNKNOWN		= 0;
	public const GENDER_FEMALE		= 1;
	public const GENDER_MALE		= 2;
	public const GENDER_OTHER		= 3;

	public const GENDERS			= [
		self::GENDER_UNKNOWN,
		self::GENDER_FEMALE,
		self::GENDER_OTHER,
		self::GENDER_OTHER,
	];

	public const STATUS_DISABLED	= -2;
	public const STATUS_BANNED		= -1;
	public const STATUS_UNCONFIRMED	= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_BANNED,
		self::STATUS_UNCONFIRMED,
		self::STATUS_ACTIVE,
	];

	public const STATUS_TRANSITIONS	= [
		self::STATUS_DISABLED		=> [
			self::STATUS_UNCONFIRMED,
			self::STATUS_ACTIVE,
		],
		self::STATUS_BANNED			=> [
			self::STATUS_DISABLED,
			self::STATUS_UNCONFIRMED,
			self::STATUS_ACTIVE,
		],
		self::STATUS_UNCONFIRMED	=> [
			self::STATUS_DISABLED,
			self::STATUS_BANNED,
			self::STATUS_ACTIVE,
		],
		self::STATUS_ACTIVE			=> [
			self::STATUS_DISABLED,
			self::STATUS_BANNED,
		],
	];

	protected string $name			= 'users';

	protected array $columns		= [
		'userId',
		'accountId',
		'roleId',
		'roomId',
		'companyId',
		'status',
		'email',
		'username',
		'password',																					//  @todo remove after old user password support decayed
		'gender',
		'salutation',
		'firstname',
		'surname',
		'country',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'createdAt',
		'modifiedAt',
		'loggedAt',
		'activeAt',
	];
	protected string $primaryKey	= 'userId';

	protected array $indices		= [
		'accountId',
		'roleId',
		'roomId',
		'companyId',
		'status',
		'username',
		'email',
		'gender',
		'country',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_User';

	public function migrate(): void
	{
		$dbc		= $this->env->getDatabase();

		/**
		 *		Merge street and number.
		 */
		$query		= "SELECT * FROM `%susers` WHERE LENGTH(number) > 0";
		$query		= sprintf( $query, (string) $dbc->getPrefix() );
		$users		= $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( count( $users ) ){
			$modelUser	= new Model_User( $this->env );
			foreach( $users as $user )
				$modelUser->edit( $user->userId,  [
					'street'	=> $user->street.' '.$user->number,
					'number'	=> NULL,
				] );
		}

		/**
		 *		Set default country to 'DE'.
		 */
		$query		= "SELECT * FROM `%susers` WHERE LENGTH(country) = 0";
		$query		= sprintf( $query, (string) $dbc->getPrefix() );
		$users		= $dbc->query( $query )->fetchAll( PDO::FETCH_OBJ );
		if( count( $users ) ){
			$modelUser	= new Model_User( $this->env );
			foreach( $users as $user )
				$modelUser->edit( $user->userId, [
					'country'	=> 'DE',
				] );
		}
	}
}
