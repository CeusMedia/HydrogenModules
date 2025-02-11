<?php
/**
 *	OAuth Access Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Access Token Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Oauth_AccessToken extends Model
{
	protected string $name			= 'oauth_access_tokens';

	protected array $columns		= [
		'oauthAccessTokenId',
		'oauthApplicationId',
		'token',
		'userId',
		'scope',
		'createdAt',
	];

	protected string $primaryKey	= 'oauthAccessTokenId';

	protected array $indices		= [
		'oauthApplicationId',
		'userId',
		'token',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	Returns generated unique token.
	 *	@access		public
	 *	@param		integer			$applicationId		ID of application to get token for
	 *	@param		string			$scope				List of scopes to get token for (optional)
	 *	@param		string|NULL		$salt				Token hash salt (optional)
	 *	@param		string|NULL		$pepper				Token hash pepper (optional)
	 *	@return		string			Token (32 characters)
	 */
	public function getNewToken( $applicationId, string $scope = '', ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		do{
			$key	= $applicationId.'_'.$scope.'_'.$salt.'_'.microtime( TRUE ).'_'.$pepper;
			$token	= md5( $key );
		} while( $this->getByIndex( 'token', $token ) );
		return $token;
	}
}
