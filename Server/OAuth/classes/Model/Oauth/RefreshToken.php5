<?php
/**
 *	OAuth Refresh Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	OAuth Refresh Token Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Oauth_RefreshToken extends CMF_Hydrogen_Model {

	protected $name		= 'oauth_refresh_tokens';
	protected $columns	= array(
		'oauthRefreshTokenId',
		'oauthApplicationId',
		'token',
		'scope',
		'createdAt',
	);
	protected $primaryKey	= 'oauthRefreshTokenId';
	protected $indices		= array(
		'oauthApplicationId',
		'token',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;

	/**
	 *	Returnes generated unique token.
	 *	@access		public
	 *	@param		integer		$applicationId		ID of application to get token for
	 *	@param		string		$scope				List of scopes to get token for (optional)
	 *	@param		string		$salt				Token hash salt (optional)
	 *	@param		string		$pepper				Token hash pepper (optional)
	 *	@return		string		Token (32 characters)
	 */
	public function getNewToken( $applicationId, $scope = "", $salt = NULL, $pepper = NULL ){
		do{
			$key	= $applicationId.'_'.$scope.'_'.$salt.'_'.microtime( TRUE ).'_'.$pepper;
			$token	= md5( $key );
		}
		while( $this->getByIndex( 'token', $token ) );
		return $token;
	}
}
?>
