<?php

use CeusMedia\HydrogenFramework\Environment;

/**
 *	Logic of user password handling.
 *	This is a singleton.
 *	@todo		extend from frameworks single logic once it exists: CeusMedia\HydrogenFramework\Logic\Singleton
 */
class Logic_UserPassword
{
	protected static self $instance;

	protected Environment $env;
	protected Model_User_Password $model;
	protected bool $useSalt;
	protected string $defaultSaltAlgo;
	protected string $defaultSaltLength;
	protected string $maxAgeBeforeDecay;

	/**
	 *	Activates a new password.
	 *	If there is a currently active password, it will be revoked.
	 *	If there are other new passwords, they will be revoked.
	 *
	 *	To inform user about password change, an E-Mail could to be sent afterward.
	 *
	 *	@access		public
	 *	@param		int|string		$userPasswordId		ID of new user password entry
	 *	@return		boolean
	 *	@throws		OutOfRangeException		if given user password ID is not existing
	 *	@throws		OutOfRangeException		if new password already has been activated, decayed or revoked
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function activatePassword( int|string $userPasswordId ): bool
	{
		$new		= $this->model->get( $userPasswordId );
		if( !$new )
			throw new OutOfRangeException( 'Invalid user password ID' );
		if( $new->status != Model_User_Password::STATUS_NEW )
			throw new OutOfRangeException( 'User password cannot be activated' );

		$old	= $this->model->getByIndices( [
			'userId'	=> $new->userId,
			'status'	=> Model_User_Password::STATUS_ACTIVE,
		] );
		if( $old ){
			$this->model->edit( $old->userPasswordId, [
				'status'	=> Model_User_Password::STATUS_REVOKED,
				'revokedAt'	=> time(),
			] );
		}
		$this->model->edit( $userPasswordId, [
			'status'		=> Model_User_Password::STATUS_ACTIVE,
			'activatedAt'	=> time(),
		] );
		return TRUE;
	}

	/**
	 *	Adds a new password to database.
	 *	ATTENTION: This new password is NOT active and must be confirmed.
	 *	It is just a possible password replacement, which needs to be activated.
	 *
	 *	To ensure correct user interaction, an E-Mail with confirmation link needs to be sent.
	 *	After confirmation the new password can be activated.
	 *	Duration activation a possibly existing currently active password will be revoked.
	 *
	 *	If salting passwords is enabled, a salt will be generated and stored with the password.
	 *
	 *	@access		public
	 *	@param		int|string		$userId			ID of user to add password for
	 *	@param		string			$password		The new password to set.
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addPassword( int|string $userId, string $password ): string
	{
		$salt	= $this->generateSalt();															//  generate password salt
		$other	= $this->model->getByIndices( [
			'userId'	=> $userId,
			'status'	=> Model_User_Password::STATUS_NEW,
		] );
		if( $other ){																				//  find other new password
			$this->model->edit( $other->userPasswordId, [										//  and revoke it
				'status'	=> Model_User_Password::STATUS_REVOKED,
				'revokedAt' => time()
			] );
		}
		$data	= [
			'userId'	=> $userId,
			'status'	=> Model_User_Password::STATUS_NEW,
			'algo'		=> PASSWORD_BCRYPT,
			'salt'		=> $salt,
			'hash'		=> $this->encryptPassword( $salt.$password, PASSWORD_BCRYPT ),
			'createdAt'	=> time(),
		];
		return $this->model->add( $data );												//  add new password and return user password ID
	}

	/**
	 *	Decays new passwords, which have not been activated or revoked.
	 *	Will to nothing, if max age of new password is not set.
	 *
	 *	To inform user about decayed password, an E-Mail could be sent afterward (not common).
	 *
	 *	ATTENTION: This method should be called by a job, only!
	 *
	 *	@access		public
	 *	@return		integer			Number of decayed passwords replacement entries
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function clearOutdatedPasswordReplacements(): int
	{
		$count	= 0;
		if( $this->maxAgeBeforeDecay ){
			foreach( $this->model->getAllByIndex( 'status', Model_User_Password::STATUS_NEW ) as $item ){
				if( time() - $item->createdAt > $this->maxAgeBeforeDecay ){
					$data	= [
						'status'	=> Model_User_Password::STATUS_OUTDATED,
						'revokedAt'	=> time(),
					];
					$count	+= (int) (bool) $this->model->edit( $item->userPasswordId, $data );
				}
			}
		}
		return $count;
	}

	/*
	public function decayPassword( $userPasswordId ){
		$password		= $this->model->get( $userPasswordId );
		if( !$password )
			throw new OutOfRangeException( 'Invalid user password ID' );
		if( $password->status != Model_User_Password::STATUS_NEW )
			throw new OutOfRangeException( 'User password cannot be decayed' );
		return (bool) $this->model->edit( $userPasswordId, array(
			'status'	=> Model_User_Password::STATUS_OUTDATED,
			'revokedAt'	=> time(),
		) );
	}
*/
	/**
	 *	Returns hash of encrypted password.
	 *	ATTENTION: You need to prepend a password salt, if enabled!
	 *
	 *	@access		public
	 *	@param		string			$password		Password to encrypt (prefixed by salt)
	 *	@param		integer|string	$algo			Encryption algorithm to use (default: PASSWORD_BCRYPT)
	 *	@return		string			Hash of encrypted (salted) password
	 *	@see		http://php.net/manual/en/password.constants.php
	 */
	public function encryptPassword( string $password, string|int $algo = PASSWORD_BCRYPT ): string
	{
		$options	= [];
		return password_hash( $password, $algo, $options );
	}

	public function getActivatableUserPassword( int|string $userId, string $password ): ?object
	{
		$indices	= [
			'userId'	=> $userId,
			'status'	=> Model_User_Password::STATUS_NEW,
		];
		foreach( $this->model->getAllByIndices( $indices ) as $item )
			if( $this->validatePassword( $item->salt.$password, $item->hash ) )
				return $item;
		return NULL;
	}

	/**
	 *	Get singleton instance of this logic class.
	 *	@static
	 *	@access		public
	 *	@param  	Environment		$env		Environment object
	 *	@return		self			Singleton instance of this logic class
	 */
	public static function getInstance( Environment $env ): self
	{
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	Indicates whether an active password has been set for user.
	 *
	 *	@access		public
	 *	@param		int|string		$userId		ID of user to check for password
	 *	@return		boolean
	 */
	public function hasUserPassword( int|string $userId ): bool
	{
		$indices	= [
			'userId'	=> $userId,
			'status'	=> Model_User_Password::STATUS_ACTIVE,
		];
		return (bool) $this->model->count( $indices );
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		string			$password
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function migrateOldUserPassword( int|string $userId, string $password ): void
	{
		if( !$this->hasUserPassword( $userId ) ){
			$userPasswordId		= $this->addPassword( $userId, $password );
			$this->activatePassword( $userPasswordId );
		}
		$model	= new Model_User( $this->env );
		$model->edit( $userId, ['password' => ''] );
	}

	/**
	 *	Indicates whether a given passwords is matching with a hash of encrypted password.
	 *	ATTENTION: You need to prepend salt to password if encrypted password has been salted!
	 *	@access		public
	 *	@param		string		$password	Password (prefixed by salt)
	 *	@param		string		$hash		Hash of encrypted password to check against
	 *	@return		boolean
	 */
	public function validatePassword( string $password, string $hash ): bool
	{
		return password_verify( $password, $hash );
	}

	/**
	 *	Indicates whether a given user password is active.
	 *
	 *	@access		public
	 *	@param		int|string		$userId			ID of user to check for password
	 *	@param		string			$password		Password to check for user
	 *	@param		boolean			$resetFails		Flag: reset fail counter on success, default: yes
	 *	@return		boolean
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function validateUserPassword( int|string $userId, string $password, bool $resetFails = TRUE ): bool
	{
		$item	= $this->model->getByIndices( [
			'userId'	=> $userId,
			'status'	=> Model_User_Password::STATUS_ACTIVE,
		] );
		if( $item && $this->validatePassword( $item->salt.$password, $item->hash ) ){
			if( $resetFails ){
				$this->model->edit( $item->userPasswordId, array(
					'usedAt'	=> time(),
					'failsLast'	=> 0
				) );
			}
			return TRUE;
		}
		return FALSE;
	}

	//  --  PROTECTED  --  //

	/**
	 *	Protected constructor - this is a singleton.
	 *	@access		protected
	 *	@param		Environment		$env		Environment object
	 */
	protected function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->model	= new Model_User_Password( $env );
		$config			= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$this->useSalt				= $config->get( 'password.salt' );
		$this->defaultSaltAlgo		= $config->get( 'password.salt.algo' );
		$this->defaultSaltLength	= $config->get( 'password.salt.length' );
		$this->maxAgeBeforeDecay	= $config->get( 'password.salt.decay.seconds' );
//		$this->clearOutdatedPasswordReplacements();
	}

	/**
	 *	Protected clone - this is a singleton.
	 *	@access		protected
	 */
	protected function __clone(){}

	/**
	 *	Generates a hash of salting passwords, if enabled.
	 *	Prepend this salt hash to the password afterward for comparisons.
	 *	Will do nothing is salting is disabled or salt hash length is 0 or lower.
	 *
	 *	Default salt generation algorithm is defined in module configuration.
	 *	At the moment, md5 over micro-time will be used for hash generation.
	 *	So maximum salt hash length is 32.
	 *	Default length of salt is defined in module configuration.
	 *
	 *	@access		protected
	 *	@param 		string|NULL		$algo		Algorithm to use to generate salt, default: by module config
	 *	@param 		integer|NULL	$length		Length of salt hash (default: by module config, max: 32)
	 *	@return		string
	 */
	protected function generateSalt( ?string $algo = NULL, ?int $length = NULL ): string
	{
		if( is_null( $algo ) )
			$algo		= $this->defaultSaltAlgo;
		if( is_null( $length ) )
			$length		= $this->defaultSaltLength;
		if( !$this->useSalt || $length < 1 )
			return '';
		switch( $algo ){
			case 'md5(microtime)':
			default:
				$length	= min( 32, $length );
				$hash	= md5( microtime( TRUE ) );
				$salt	= substr( $hash, 0, $length );
				break;
		}
		return $salt;
	}
}
