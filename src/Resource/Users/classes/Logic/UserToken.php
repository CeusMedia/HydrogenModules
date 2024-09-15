<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic;

/**
 * @todo check if deprecated, seems to be not used, right? if so, remove class
 */
class Logic_UserToken extends Logic
{
	protected Model_User $modelUser;
	protected Model_User_Password $modelPassword;
	protected Model_User_Token $modelToken;

	/**
	 *	@param		string		$username
	 *	@param		string		$password
	 *	@param		?string		$scope
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( string $username, string $password, ?string $scope = NULL ): string
	{
		//  check username
		$userId	= $this->getUserIdFromUsername( $username );

		//  check password
		if( !$this->validateUserPassword( $userId, $password ) )
			throw new RangeException( 'Invalid password' );

		//  create new token
		$token		= ID::uuid();
		$tokenId	= $this->modelToken->add( [
			'userId'	=> $userId,
			'status'	=> Model_User_Token::STATUS_ACTIVE,
			'token'		=> $token,
			'scope'		=> (string) $scope,
			'createdAt'	=> time(),
		] );

		$this->revokeByUserId( $userId, $tokenId );

		return $token;
	}

	/**
	 *	@param		string		$token
	 *	@param		?string		$username
	 *	@param		?string		$scope
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function validate( string $token, ?string $username, ?string $scope = NULL ): bool
	{
		$indices	= [
			'scope'		=> (string) $scope,
			'token'		=> $token,
			'status'	=> Model_User_Token::STATUS_ACTIVE,
		];
		if( strlen( trim( $username ) ) > 0 )
			$indices['userId']	= $this->getUserIdFromUsername( $username );

		$item		= $this->modelToken->getByIndices( $indices );
		if( !$item )
			return FALSE;

		$this->modelToken->edit( $item->userTokenId, ['usedAt' => time()] );
		return TRUE;
	}

	/**
	 *	@param		string		$token
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function revokeByToken( string $token ): bool
	{
		return $this->revokeByTokenId( $token );
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		string|NULL		$except
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function revokeByUserId( int|string $userId, string $except = NULL ): bool
	{
		$indices	= [
			'userId'		=> $userId,
			'status'		=> [
				Model_User_Token::STATUS_NEW,
				Model_User_Token::STATUS_ACTIVE
			],
		];
		if( strlen( trim( $except ) ) > 0 )
			$indices['userTokenId']	= '!= '.$except;
		$tokens	= $this->modelToken->getAllByIndices( $indices );
		foreach( $tokens as $token )
			$this->revokeByTokenId( $token->userTokenId );
		return count( $tokens ) > 0;
	}

	/**
	 *	@param		string		$scope
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function revokeByScope( string $scope ): bool
	{
		$indices	= [
			'scope'		=> $scope,
			'status'	=> [
				Model_User_Token::STATUS_NEW,
				Model_User_Token::STATUS_ACTIVE
			],
		];
		$tokens	= $this->modelToken->getAllByIndices( $indices );
		foreach( $tokens as $token )
			$this->revokeByTokenId( $token->userTokenId );
		return count( $tokens ) > 0;
	}

	/**
	 *	@param		string		$username
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function revokeByUsername( string $username ): bool
	{
		$userId	= $this->getUserIdFromUsername( $username );
		return $this->revokeByUserId( $userId );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		string		$token
	 *	@return		object
	 */
	protected function getToken( string $token ): object
	{
		if( strlen( trim( $token ) ) === 0 )
			throw new InvalidArgumentException( 'No token given' );
		$token	= $this->modelToken->getByIndex( 'token', $token );
		if( !$token )
			throw new RangeException( 'Invalid token' );
		return $token;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelUser		= new Model_User( $this->env );
		$this->modelPassword	= new Model_User_Password( $this->env );
		$this->modelToken		= new Model_User_Token( $this->env );
	}

	/**
	 *	@param		string		$username
	 *	@return		string
	 */
	protected function getUserIdFromUsername( string $username ): string
	{
		//  validate input
		if( 0 === strlen( trim( $username ) ) )
			throw new InvalidArgumentException( 'No username given' );

		//  check username
		$user	= $this->modelUser->getByIndex( 'username', $username );
		if( !$user )
			throw new DomainException( 'Invalid username' );

		return $user->userId;
	}

	/**
	 *	@param		int|string		$tokenId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function revokeByTokenId( int|string $tokenId ): bool
	{
		return (bool) $this->modelToken->edit( $tokenId, [
			'status'	=> Model_User_Token::STATUS_REVOKED,
			'revokedAt'	=> time(),
		] );
	}

	/**
	 *	@param		string		$userId
	 *	@param		string		$password
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function validateUserPassword( string $userId, string $password ): bool
	{
		if( 0 === strlen( trim( $password ) ) )
			throw new InvalidArgumentException( 'No password given' );
		$item	= $this->modelPassword->getByIndices( [
			'userId' 	=> $userId,
			'status'	=> Model_User_Password::STATUS_ACTIVE,
		] );
		if( !$item )
			throw new RangeException( 'No password set for user' );
		//  @todo support password pepper by using password.pepper from module config
		$spicedPassword	= $item->salt.$password;//.$pepper;
		if( !password_verify( $spicedPassword, $item->hash ) ){
			$this->modelPassword->edit( $item->userPasswordId, [
				'failedAt'	=> time(),
				'failsLast'	=> $item->failsLast + 1,
			] );
			return FALSE;
		}

		//  note, that password has been used and reset fail counter
		$this->modelPassword->edit( $item->userPasswordId, [
			'usedAt'	=> time(),
			'failsLast'	=> 0
		] );
		return TRUE;
	}
}
