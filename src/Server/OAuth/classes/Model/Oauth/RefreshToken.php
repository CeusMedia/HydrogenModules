<?php
/**
 *	OAuth Refresh Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Refresh Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Oauth_RefreshToken extends Model
{
	protected string $name			= 'oauth_refresh_tokens';

	protected array $columns		= [
		'oauthRefreshTokenId',
		'oauthApplicationId',
		'token',
		'scope',
		'createdAt',
	];

	protected string $primaryKey	= 'oauthRefreshTokenId';

	protected array $indices		= [
		'oauthApplicationId',
		'token',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	Returns generated unique token.
	 *	@access		public
	 *	@param		int|string		$applicationId		ID of application to get token for
	 *	@param		string			$scope				List of scopes to get token for (optional)
	 *	@param		string|NULL		$salt				Token hash salt (optional)
	 *	@param		string|NULL		$pepper				Token hash pepper (optional)
	 *	@return		string			Token (32 characters)
	 */
	public function getNewToken( int|string $applicationId, string $scope = '', ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		do{
			$key	= $applicationId.'_'.$scope.'_'.$salt.'_'.microtime( TRUE ).'_'.$pepper;
			$token	= md5( $key );
		}
		while( $this->getByIndex( 'token', $token ) );
		return $token;
	}
}
