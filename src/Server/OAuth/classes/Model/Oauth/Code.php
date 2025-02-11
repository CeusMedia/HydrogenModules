<?php
/**
 *	OAuth Code Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	OAuth Code Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Oauth_Code extends Model
{
	protected string $name			= 'oauth_codes';

	protected array $columns		= [
		'oauthCodeId',
		'oauthApplicationId',
		'userId',
		'redirectUri',
		'code',
		'scope',
		'createdAt',
	];

	protected string $primaryKey	= 'oauthCodeId';

	protected array $indices		= [
		'oauthApplicationId',
		'userId',
		'redirectUri',
		'code',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;

	/**
	 *	Returns generated unique token.
	 *	@access		public
	 *	@param		integer		$applicationId		ID of application to get token for
	 *	@param		string		$scope				List of scopes to get token for (optional)
	 *	@return		string		Authorization code (32 characters)
	 */
	public function getNewCode( $applicationId, string $scope = '' ): string
	{
		do{
			$code	= md5( $applicationId.'_'.$scope.'_'.microtime( TRUE ) );
		}
		while( $this->getByIndex( 'code', $code ) );
		return $code;
	}
}
