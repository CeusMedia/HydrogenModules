<?php
class Logic_UserToken extends CMF_Hydrogen_Logic
{
	protected $modelUser;
	protected $modelPassword;
	protected $modelToken;

	public function authenticate()
	{
		return $this->get();
	}

	public function get( string $username, string $password ): string
	{
		//  check username
		$userId	= $this->getUserIdFromUsername( $username );

		//  check password
		if( !$this->validateUserPassword( $userId, $password ) )
			throw new RangeException( 'Invalid password' );

		//  create new token
		$token		= Alg_ID::uuid();
		$tokenId	= $this->modelToken->add( array(
			'userId'	=> $userId,
			'status'	=> Model_User_Token::STATUS_ACTIVE,
			'token'		=> $token,
			'createdAt'	=> time(),
		) );

		$this->revokeByUserId( $userId, $tokenId );

		return $token;
	}

	public function validate( string $token, ?string $username ): bool
	{
		$indices	= array(
			'token'		=> $token,
			'status'	=> Model_User_Token::STATUS_ACTIVE,
		);
		if( strlen( trim( $username ) ) > 0 )
			$indices['userId']	= $this->getUserIdFromUsername( $username );

		$item		= $this->modelToken->getByIndices( $indices );
		if( !$item )
			return FALSE;

		$this->modelToken->edit( $item->userTokenId, array( 'usedAt' => time() ) );
		return TRUE;
	}

	public function revokeByToken( string $token ): bool
	{
		return $this->revokeByTokenId( $tokenId );
	}

	public function revokeByUserId( $userId, string $except = NULL ): bool
	{
		$indices	= array(
			'userId'		=> $user->userId,
			'status'		=> array(
				Model_User_Token::STATUS_NEW,
				Model_User_Token::STATUS_ACTIVE
			),
		);
		if( strlen( trim( $except ) ) > 0 )
			$indices['userTokenId']	= '!= '.$except;
		$tokens	= $this->modelToken->getAllByIndices( $indices );
		foreach( $tokens as $token )
			$this->revokeByTokenId( $token->userTokenId );
		return count( $tokens ) > 0;
	}

	public function revokeByUsername( string $username ): bool
	{
		$userId	= $this->getUserIdFromUsername( $username );
		return $this->revokeByUserId( $userId );
	}

	//  --  PROTECTED  --  //

	protected function getToken( $token )
	{
		if( strlen( trim( $token ) ) === 0 )
			throw new InvalidArgumentException( 'No token given' );
		$token	= $this->modelToken->getByIndex( 'token', $token );
		if( !$token )
			throw new RangeException( 'Invalid token' );
		return $token;
	}

	protected function __onInit()
	{
		$this->modelUser		= new Model_User( $this->env );
		$this->modelPassword	= new Model_User_Password( $this->env );
		$this->modelToken		= new Model_User_Token( $this->env );
	}

	protected function getUserIdFromUsername( string $username ): string
	{
		//  validate input
		if( strlen( trim( $username ) ) === 0 )
			throw new InvalidArgumentException( 'No username given' );

		//  check username
		$user	= $this->modelUser->getByIndex( 'username', $username );
		if( !$user )
			throw new DomainException( 'Invalid username' );

		return $user->userId;
	}

	protected function revokeByTokenId( string $tokenId ): bool
	{
		return (bool) $this->modelToken->edit( $tokenId, array(
			'status'	=> Model_User_Token::STATUS_REVOKED,
			'revokedAt'	=> time(),
		) );
	}

	protected function validateUserPassword( string $userId, string $password ): bool
	{
		if( strlen( trim( $password ) ) === 0 )
			throw new InvalidArgumentException( 'No password given' );
		$item	= $this->modelPassword->getByIndices( array(
			'userId' 	=> $user->userId,
			'status'	=> Model_User_Password::STATUS_ACTIVE,
		) );
		if( !$item )
			throw new RangeException( 'No password set for user' );
		//  @todo support password pepper by using password.pepper from module config
		$spicedPassword	= $item->salt.$password;//.$pepper;
		if( !password_verify( $spicedPassword, $item->hash ) ){
			$this->modelPassword->edit( $item->userPasswordId, array(
				'failedAt'	=> time(),
				'failsLast'	=> $item->failsLast + 1,
			) );
			return FALSE;
		}

		//  note, that password has been used and reset fail counter
		$this->modelPassword->edit( $item->userPasswordId, array(
			'usedAt'	=> time(),
			'failsLast'	=> 0
		) );
		return TRUE;
	}
}