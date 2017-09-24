<?php
class Logic_Authentication_Backend_Rest{

	static protected $instance;
	protected $env;

	protected function __construct( $env ){
		$this->env			= $env;
		$this->client		= $this->env->get( 'restClient' );
	}

	public function checkEmail( $email ){
		$parameters	= array( 'email' => $email );
		return $this->client->post( 'email/check', $parameters )->data;
	}

	public function checkPassword( $username, $password ){
		$parameters	= array(
			'username'	=> $username,
			'password'	=> $password,
		);
		$result	= $this->client->post( 'authenticate', $parameters );
		return $result;
	}

	public function checkUsername( $username ){
		$parameters	= array( 'username' => $username );
		return $this->client->post( 'username/check', $parameters )->data;
	}

	public function confirm( $userId, $token ){
		$parameters	= array(
			'userId'	=> $userId,
			'token'		=> $token,
		);
		$result	= $this->client->post( 'confirm', $parameters )->data;
		return $result;
	}

	public function getCurrentRole( $strict = TRUE ){
		throw new Exception( 'deprecated' );
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->env->getServer()->postData( 'role', 'get', array( $roleId ) );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	public function getCurrentRoleId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->env->getSession()->get( 'roleId');
	}

	public function getCurrentUser( $strict = TRUE, $withRole = FALSE ){
		throw new Exception( 'deprecated' );
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->env->getServer()->postData( 'user', 'get', array( $userId ) );
			if( $user ){
				if( $withRole )
					$user->role	= $this->env->getServer()->postData( 'role', 'get', array( $user->roleId ) );
				return $user;
			}
		}
		if( $strict )
			throw new RuntimeException( 'No valid user identified' );
		return NULL;
	}

	public function getCurrentUserId( $strict = TRUE ){
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->env->getSession()->get( 'userId' );
	}

	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	public function isAuthenticated(){
		return $this->env->getSession()->get( 'userId' );
	}

	public function isCurrentUserId( $userId ){
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible, for example by using available REST resource
	 */
	protected function noteUserActivity(){
	}

	public function register( $postData ){

		$data	= array(
			'username'		=> $postData->get( 'username' ),
			'email'			=> $postData->get( 'email' ),
			'phone'			=> $postData->get( 'phone' ),
		);
		if( $postData->get( 'business' ) ){
			$data	= array_merge( $data, array(
				'company'	=> $postData->get( 'company' ),
				'tax_id'	=> $postData->get( 'tax_id' ),
			) );
		}
		$responseAccount	= $this->client->post( 'account', $data );
		if( $responseAccount->data < 1 )
			return 'account:'.$responseAccount->data;

		$accountId	= $responseAccount->data;
		$data	= array(
			'account_id'	=> $accountId,
			'type'			=> 0,
			'country'		=> $postData->get( 'country' ),
			'state'			=> $postData->get( 'state' ),
			'postcode'		=> $postData->get( 'postcode' ),
			'city'			=> $postData->get( 'city' ),
			'street'		=> $postData->get( 'street' ),
			'email'			=> $postData->get( 'email' ),
			'phone'			=> $postData->get( 'phone' ),
		);
		$url		= sprintf( 'account/%d/address', $accountId );
		$responseAddress	= $this->client->post( $url, $data );

		if( $responseAddress->data < 1 ){
			return 'address:'.$responseAddress->data;
		}

		if( $postData->get( 'billing_address' ) ){
			$data	= array(
				'account_id'	=> $accountId,
				'type'			=> 1,
				'country'		=> $postData->get( 'billing_country' ),
				'state'			=> $postData->get( 'billing_state' ),
				'postcode'		=> $postData->get( 'billing_postcode' ),
				'city'			=> $postData->get( 'billing_city' ),
				'street'		=> $postData->get( 'billing_street' ),
				'phone'			=> $postData->get( 'billing_phone' ),
				'email'			=> $postData->get( 'billing_email' ),
			);
			$url				= sprintf( 'account/%d/address', $accountId );
			$responseBilling	= $this->client->post( $url, $data );
			if( $responseBilling->data < 1 ){
				return 'billing:'.$responseBilling->data;
			}
		}
		return array(
			'accountId'	=> $responseAccount->data,
			'addressId'	=> $responseAddress->data,
			'billingId'	=> $responseBilling->data,
		);
	}

/*	public function setCurrentUser( $userId ){


		$this->env->getSession()->set( 'userId', $userId );
		$this->env->getSession()->set( 'userId', $userId );
	}*/
}
