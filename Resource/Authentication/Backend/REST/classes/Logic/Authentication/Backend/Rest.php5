<?php
class Logic_Authentication_Backend_Rest extends CMF_Hydrogen_Logic
{
	protected $client;
	protected $session;

	public function checkEmail( string $email )
	{
		$parameters	= array( 'email' => $email );
		return $this->client->post( 'email/check', $parameters )->data;
	}

	public function checkPassword( string $username, string $password )
	{
		$parameters	= array(
			'username'	=> $username,
			'password'	=> $password,
		);
		$result	= $this->client->post( 'authenticate', $parameters );
		return $result;
	}

	public function checkUsername( string $username )
	{
		$parameters	= array( 'username' => $username );
		return $this->client->post( 'username/check', $parameters )->data;
	}

	public function clearCurrentUser()
	{
		$this->session->remove( 'auth_user_id' );
		$this->session->remove( 'auth_role_id' );
		$this->session->remove( 'auth_status' );
		$this->session->remove( 'auth_account_id' );
		$this->session->remove( 'auth_token' );
		$this->session->remove( 'auth_rights' );
		$this->session->remove( 'auth_type' );
		$this->session->remove( 'auth_username' );
		$this->env->getCaptain()->callHook( 'Auth', 'clearCurrentUser', $this );
	}

	/**
 	 *	@todo		send mail to user after confirmation with user data
	 */
	public function confirm( $userId, string $token )
	{
		$parameters	= array(
			'userId'	=> $userId,
			'token'		=> $token,
		);
		$result	= $this->client->post( 'confirm', $parameters )->data;
		return $result;
	}

	public function getCurrentRole( bool $strict = TRUE )
	{
return NULL;
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->client->post( 'role/get', array( $roleId ) );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	public function getCurrentRoleId( bool $strict = TRUE )
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( 'roleId');
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE )
	{
return NULL;
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->client->post( 'user/get', array( $userId ) );
			if( $user ){
				$user->role	= $withRole ? $this->getCurrentRole() : NULL;
				return $user;
			}
		}
		if( $strict )
			throw new RuntimeException( 'No valid user identified' );
		return NULL;
	}

	public function getCurrentUserId( bool $strict = TRUE )
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->session->get( 'auth_user_id' );
	}

	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( 'auth_status' );
		return $authStatus == Logic_Authentication::STATUS_AUTHENTICATED;
	}

	public function isIdentified(): bool
	{
		return (bool) $this->session->get( 'auth_user_id' );
	}

	public function isCurrentUserId( $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible, for example by using available REST resource
	 */
	public function noteUserActivity()
	{
	}

	/**
 	 *	@todo		send mail to user with confirmation link
	 */
	public function register( $postData )
	{
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

	public function setAuthenticatedUser( $user )
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( $user )
	{
		$this->session->set( 'auth_backend', 'Rest' );
		$this->session->set( 'auth_user_id', $user->data->userId );
		$this->session->set( 'auth_role_id', $user->data->roleId );
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_IDENTIFIED );
		$this->session->set( 'auth_account_id', $user->data->accountId );
		$this->session->set( 'auth_token', $user->data->token );
		$this->session->set( 'auth_rights', $user->data->rights );
		$this->session->set( 'auth_type', $user->data->loginType );
		$this->session->set( 'auth_username', $user->data->username );
		return $this;
	}

	protected function __onInit()
	{
		$this->client		= $this->env->get( 'restClient' );
		$this->session		= $this->env->getSession();
	}
}
