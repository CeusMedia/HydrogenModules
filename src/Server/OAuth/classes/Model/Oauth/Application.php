<?php
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;
//use Random\RandomException;

/**
 *	OAuth Code Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Oauth_Application extends Model
{
	public const STATUS_REMOVED		= -1;
	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;

	public const STATUSES			= [
		self::STATUS_REMOVED,
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

	public const TYPE_PUBLIC		= 0;
	public const TYPE_CONFIDENTIAL	= 1;

	public const TYPES				= [
		self::TYPE_PUBLIC,
		self::TYPE_CONFIDENTIAL,
	];

	protected string $name			= 'oauth_applications';

	protected array $columns		= [
		'oauthApplicationId',
		'userId',
		'type',
		'status',
		'clientId',
		'clientSecret',
		'title',
		'description',
		'url',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'oauthApplicationId';

	protected array $indices		= [
		'userId',
		'type',
		'status',
		'clientId',
		'clientSecret',
		'url',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	@param		string|NULL		$salt
	 *	@param		string|NULL		$pepper
	 *	@return		string
	 *	@throws		RandomException
	 */
	public function getNewClientId( ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		do{
			$pos	= round( random_int(0, 1) * 20 );
			$id		= substr( md5( $salt.'_'.microtime( TRUE ).'_'.$pepper ), $pos, 12 );
		} while( $this->getByIndex( 'clientId', $id ) );
		return $id;
	}

	/**
	 *	@param		string|NULL		$salt
	 *	@param		string|NULL		$pepper
	 *	@return		string
	 */
	public function getNewClientSecret( ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		return hash( "sha256", $salt.'_'.microtime( TRUE ).'_'.$pepper );
	}

	/**
	 *	@param		int|string		$id
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $id ): bool
	{
		$modelAccess	= new Model_Oauth_AccessToken( $this->env );
		$modelCode		= new Model_Oauth_Code( $this->env );
		$modelRefresh	= new Model_Oauth_RefreshToken( $this->env );
		$modelAccess->removeByIndex( 'oauthApplicationId', $id );
		$modelCode->removeByIndex( 'oauthApplicationId', $id );
		$modelRefresh->removeByIndex( 'oauthApplicationId', $id );
		return parent::remove( $id );
	}
}
