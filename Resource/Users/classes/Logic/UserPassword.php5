<?php
class Logic_UserPassword{

	static protected $instance;

	protected $env;
	protected $model;
	protected $useSalt;
	protected $defaultSaltAlgo;
	protected $defaultSaltLength;
	protected $maxAgeBeforeDecay;

	protected function __construct( $env ){
		$this->env		= $env;
		$this->model	= new Model_User_Password( $env );
		$config			= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$this->useSalt				= $config->get( 'password.salt' );
		$this->defaultSaltAlgo		= $config->get( 'password.salt.algo' );
		$this->defaultSaltLength	= $config->get( 'password.salt.length' );
		$this->maxAgeBeforeDecay	= $config->get( 'password.salt.decay.seconds' );
//		$this->clearOutdatedPasswordReplacements();
	}

	protected function __clone(){}

	/**
	 *	Activates a new password.
	 *	If there is a currently active password, it will be revoked.
	 *	If there are other new passwords, they will be revoked.
	 *
	 *	To inform user about password change, an E-Mail could to be sent afterwards.
	 *
	 *	@access		public
	 *	@param   	integer		$userPasswordId		ID of new user password entry
	 *	@return		boolean
	 *	@throws		OutOfRangeException		if given user password ID is not existing
	 *	@throws		OutOfRangeException		if new password already has been activated, decayed or revoked
	 */
	public function activatePassword( $userPasswordId ){
		$new		= $this->model->get( $userPasswordId );
		if( !$new )
			throw new OutOfRangeException( 'Invalid user password ID' );
		if( $new->status != 0 )
			throw new OutOfRangeException( 'User password cannot be activated' );

		$old	= $this->model->getByIndices( array( 'userId' => $new->userId, 'status' => 1 ) );
		if( $old ){
			$this->model->edit( $old->userPasswordId, array(
				'status'	=> -2,
				'revokedAt'	=> time(),
			) );
		}
		$this->model->edit( $userPasswordId, array(
			'status'		=> 1,
			'activatedAt'	=> time(),
		) );
/*		$others	= $this->model->getAllByIndices( array( 'userId' => $userId, 'status' => 0 ) );
		foreach( $others as $item ){
			$this->model->edit( $item->userPasswordId, array(
				'status'	=> -1,
				'revokedAt'	=> time(),
			) );
		}*/
		return TRUE;
	}

	/**
	 *	Adds a new password to database.
	 *	ATTENTION: This new password is NOT active and must be confirmed.
	 *	It is just a possible password replacement, which needs to be activated.
	 *
	 *	To ensure correct user interaction, an E-Mail with confirmation link needs to be sent.
	 *	After confirmation the new password can be activated.
	 *	Duration activation a possbily existing currently active password will be revoked.
	 *
	 *	If salting passwords is enabled, a salt will be generated and stored with the password.
	 *
	 *	@access		public
	 *	@param 		integer		$userId			ID of user to add password for
	 *	@param 		string		$password		The new password to set.
	 *	@return		integer		$userPasswordId
	 */
	public function addPassword( $userId, $password ){
		$salt	= $this->generateSalt();															//  generate password salt
		if( $other = $this->model->getByIndices( array( 'userId' => $userId, 'status' => 0 ) ) ){	//  find other new password
			$this->model->edit( $other->userPasswordId, array(										//  and revoke it
				'status'	=> -2,
				'revokedAt' => time()
			) );
		}
		$data	= array(
			'userId'	=> $userId,
			'status'	=> 0,
			'salt'		=> $salt,
			'hash'		=> $this->encryptPassword( $salt.$password ),
			'createdAt'	=> time(),
		);
		$userPasswordId	= $this->model->add( $data );												//  add new password
		return $userPasswordId;																		//  return user password ID
	}

	/**
	 *	Decays new passwords, which have not been activated or revoked.
	 *	Will to nothing, if max age of new password is not set.
	 *
	 *	To inform user about decayed password, an E-Mail could be sent afterwards (not common).
	 *
	 *	ATTENTION: This method should be called by a job, only!
	 *
	 *	@access		public
	 *	@return		integer			Number of decayed passwords replacement entries
	 */
	public function clearOutdatedPasswordReplacements(){
		$count	= 0;
		if( $this->maxAgeBeforeDecay ){
			foreach( $this->model->getAllByIndex( 'status', 0 ) as $item ){
				if( time() - $item->createdAt > $this->maxAgeBeforeDecay ){
					$data	= array(
						'status'	=> -1,
						'revokedAt'	=> time(),
					);
					$count	+= (int) (bool) $this->model->edit( $userPasswordId, $data );
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
		if( $password->status != 0 )
			throw new OutOfRangeException( 'User password cannot be decayed' );
		return (bool) $this->model->edit( $userPasswordId, array(
			'status'	=> -1,
			'revokedAt'	=> time(),
		) );
	}
*/
	/**
	 *	Returns hash of encrypted password.
	 *	ATTENTION: You need to prepend a password salt, if enabled!
	 *
	 *	@access		public
	 *	@param    	string			Password to encrypt (prefixed by salt)
	 *	@param    	string			Encryption algorithm to use (default: bcrypt)
	 *	@return 	string			Hash of encrypted (salted) password
	 *	@see		http://php.net/manual/en/password.constants.php
	 */
	public function encryptPassword( $password, $algo = PASSWORD_BCRYPT ){
		return password_hash( $password, $algo );
	}

	/**
	 *	Generates a hash of salting passwords, if enabled.
	 *	Prepend this salt hash to the password afterwards for comparisons.
	 *	Will do nothing is salting is disabled or salt hash length is 0 or lower.
	 *
	 *	Default salt generation algorithm is defined in module configuration.
	 *	At the moment, md5 over microtime will be used for hash generation.
	 *	So maximumn salt hash length is 32.
	 *	Default length of salt is defined in module configuration.
	 *
	 *	@access		public
	 *	@param 		integer		$length		Length of salt hash (default: config, max: 32)
	 */
	protected function generateSalt( $algo = NULL, $length = NULL ){
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

	public function getActivableUserPassword( $userId, $password ){
		$indices	= array(
			'userId'	=> $userId,
			'status'	=> 0,
		);
		foreach( $this->model->getAllByIndices( $indices ) as $item )
			if( $this->validatePassword( $item->salt.$password, $item->hash ) )
				return $item;
	}

	/**
	 *	Get singleton instance of this logic class.
	 *	@static
	 *	@access		public
	 *	@param  	object    	$env		Environment object
	 *	@return		object   		Singleton instance of this logic class
	 */
	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	Indicates wheter an active password has been set for user.
	 *
	 *	@access		public
	 *	@param		integer		$userId		ID of user to check for password
	 *	@return		boolean
	 */
	public function hasUserPassword( $userId ){
		$indices	= array(
			'userId'	=> $userId,
			'status'	=> 1,
		);
		return (bool) $this->model->count( $indices );
	}

	public function migrateOldUserPassword( $userId, $password ){
		if( !$this->hasUserPassword( $userId ) ){
			$userPasswordId		= $this->addPassword( $userId, $password );
			$this->activatePassword( $userPasswordId );
		}
		$model	= new Model_User( $this->env );
		$model->edit( $userId, array( 'password' => NULL ) );
	}

	/**
	 *	Indicates wheter an given passwords matches with a hash of encrypted password.
	 *	ATTENTION: You need to prepend salt to password if encrypted password has been salted!
	 *	@access		public
	 *	@param		integer		$password	Password (prefixed by salt)
	 *	@param		integer		$hash		Hash of encrypted password to check against
	 *	@return		boolean
	 */
	public function validatePassword( $password, $hash ){
		return password_verify( $password, $hash );
	}

	/**
	 *	Indicates wheter a given user password is active.
	 *
	 *	@access		public
	 *	@param		integer		$userId		ID of user to check for password
	 *	@param		string		$password	Password to check for user
	 *	@return		boolean
	 */
	public function validateUserPassword( $userId, $password ){
		$item	= $this->model->getByIndices( array(
			'userId'	=> $userId,
			'status'	=> 1,
		) );
		if( $item && $this->validatePassword( $item->salt.$password, $item->hash ) ){
			$this->model->edit( $item->userPasswordId, array(
				'usedAt'	=> time(),
				'failsLast'	=> 0
			) );
			return TRUE;
		}
		return FALSE;
	}
}
?>
