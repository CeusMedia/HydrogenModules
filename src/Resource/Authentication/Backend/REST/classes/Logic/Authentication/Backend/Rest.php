<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\MissingExtension as NotSupportedExtension;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Rest extends Logic implements Logic_Authentication_BackendInterface
{
	protected ?Resource_REST_Client $client;
	protected Dictionary $session;

	public function authenticate( int|string $username, string $password ): object
	{
		$parameters	= [
			'username'	=> $username,
			'password'	=> $password,
		];
		return $this->client->post( 'authenticate', $parameters );
	}

	public function checkEmail( string $email )
	{
		$parameters	= ['email' => $email];
		return $this->client->post( 'email/check', $parameters )->data;
	}

	public function checkPassword( int|string $username, string $password ): bool
	{
		$parameters	= [
			'username'	=> $username,
			'password'	=> $password,
		];
		$response = $this->client->post( 'password/check', $parameters );
		return $response->data;
	}

	public function checkUsername( string $username )
	{
		$parameters	= ['username' => $username];
		return $this->client->post( 'username/check', $parameters )->data;
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function clearCurrentUser(): void
	{
		$this->session->remove( Logic_Authentication::$sessionKeyAuthUserId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthRoleId );
		$this->session->remove( Logic_Authentication::$sessionKeyAuthStatus );
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
	public function confirm( int|string $userId, string $token )
	{
		$parameters	= [
			'userId'	=> $userId,
			'token'		=> $token,
		];
		return $this->client->post( 'confirm', $parameters )->data;
	}

	/**
	 *	@param		bool		$strict
	 *	@return		object|NULL
	 *	@throws		RuntimeException	if no user authenticated
	 *	@throws		RuntimeException	if no valid role identified
	 */
	public function getCurrentRole( bool $strict = TRUE ): NULL|object
	{
		$roleId	= $this->getCurrentRoleId( $strict );
		if( $roleId ){
			$role	= $this->client->post( 'role/get', [$roleId] );
			if( $role )
				return $role;
			if( $strict )
				throw new RuntimeException( 'No valid role identified' );
		}
		return NULL;
	}

	/**
	 *	@param		bool	$strict
	 *	@return		int|string|NULL
	 *	@throws		RuntimeException	if no user authenticated
	 */
	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( Logic_Authentication::$sessionKeyAuthRoleId );
	}

	/**
	 *	@param		bool		$strict
	 *	@param		int			$extensions		Flags: extend user entity, default: Logic_User::EXTEND_NOTHING
	 *	@return		object|NULL
	 *	@throws		RuntimeException	if no valid user identified in current session
	 *	@throws		RuntimeException	if role extension and no user authenticated
	 *	@throws		RuntimeException	if role extension and valid role identified
	 */
	public function getCurrentUser( bool $strict = TRUE, int $extensions = Logic_User::EXTEND_NOTHING ): ?object
	{
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->client->post( 'user/get', [$userId] );
			if( $user ){
				if( $extensions & Logic_User::EXTEND_ROLE )
					$user->role	= $this->getCurrentRole();
				return $user;
			}
		}
		if( $strict )
			throw new RuntimeException( 'No valid user identified' );
		return NULL;
	}

	public function getCurrentUserId( bool $strict = TRUE ): int|string|null
	{
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return 0;
		}
		return $this->session->get( Logic_Authentication::$sessionKeyAuthUserId );
	}

	public function isAuthenticated(): bool
	{
		if( !$this->isIdentified() )
			return FALSE;
		$authStatus	= (int) $this->session->get( Logic_Authentication::$sessionKeyAuthStatus );
		return $authStatus === Logic_Authentication::STATUS_AUTHENTICATED;
	}

	public function isIdentified(): bool
	{
		return (bool) $this->session->get( Logic_Authentication::$sessionKeyAuthUserId );
	}

	public function isCurrentUserId( int|string $userId ): bool
	{
		return $this->getCurrentUserId( FALSE ) == $userId;
	}

	/**
	 *	@todo		implement if possible, for example by using available REST resource
	 */
	public function noteUserActivity(): self
	{
		return $this;
	}

	/**
 	 *	@todo		send mail to user with confirmation link
	 */
	public function register( Dictionary $postData ): array|string
	{
		$data	= array(
			'username'		=> $postData->get( 'username' ),
			'email'			=> $postData->get( 'email' ),
			'phone'			=> $postData->get( 'phone' ),
		);
		if( $postData->get( 'business' ) ){
			$data	= array_merge( $data, [
				'company'	=> $postData->get( 'company' ),
				'tax_id'	=> $postData->get( 'tax_id' ),
			] );
		}
		$responseAccount	= $this->client->post( 'account', $data );
		if( $responseAccount->data < 1 )
			return 'account:'.$responseAccount->data;

		$accountId	= $responseAccount->data;
		$data		= [
			'account_id'	=> $accountId,
			'type'			=> 0,
			'country'		=> $postData->get( 'country' ),
			'state'			=> $postData->get( 'state' ),
			'postcode'		=> $postData->get( 'postcode' ),
			'city'			=> $postData->get( 'city' ),
			'street'		=> $postData->get( 'street' ),
			'email'			=> $postData->get( 'email' ),
			'phone'			=> $postData->get( 'phone' ),
		];
		$url		= sprintf( 'account/%d/address', $accountId );
		$responseAddress	= $this->client->post( $url, $data );

		if( $responseAddress->data < 1 ){
			return 'address:'.$responseAddress->data;
		}

		$responseBilling	= NULL;
		if( $postData->get( 'billing_address' ) ){
			$data	= [
				'account_id'	=> $accountId,
				'type'			=> 1,
				'country'		=> $postData->get( 'billing_country' ),
				'state'			=> $postData->get( 'billing_state' ),
				'postcode'		=> $postData->get( 'billing_postcode' ),
				'city'			=> $postData->get( 'billing_city' ),
				'street'		=> $postData->get( 'billing_street' ),
				'phone'			=> $postData->get( 'billing_phone' ),
				'email'			=> $postData->get( 'billing_email' ),
			];
			$url				= sprintf( 'account/%d/address', $accountId );
			$responseBilling	= $this->client->post( $url, $data );
			if( $responseBilling->data < 1 ){
				return 'billing:'.$responseBilling->data;
			}
		}
		return [
			'accountId'	=> $responseAccount->data,
			'addressId'	=> $responseAddress->data,
			'billingId'	=> $responseBilling ? $responseBilling->data : NULL,
		];
	}

	public function setAuthenticatedUser( object $user ): self
	{
		$this->setIdentifiedUser( $user );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( object $user ): self
	{
		$this->session->set( Logic_Authentication::$sessionKeyAuthBackend, 'Rest' );
		$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->data->userId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->data->roleId );
		$this->session->set( Logic_Authentication::$sessionKeyAuthStatus, Logic_Authentication::STATUS_IDENTIFIED );
		$this->session->set( 'auth_account_id', $user->data->accountId );
		$this->session->set( 'auth_token', $user->data->token );
		$this->session->set( 'auth_rights', $user->data->rights );
		$this->session->set( 'auth_type', $user->data->loginType );
		$this->session->set( 'auth_username', $user->data->username );
		return $this;
	}

	protected function __onInit(): void
	{
		$client		= $this->env->get( 'restClient' );
		if( !$client instanceof Resource_REST_Client ){
			if( class_exists( NotSupportedExtension::class ) )
				throw NotSupportedExtension::create()
					->setMessage( 'Sorry, support for Resource_REST_Client only, atm' )
					->setSuggestion( 'You can fix this! This is open source software ;-)' );
			throw new RuntimeException( 'Sorry, support for Resource_REST_Client only, atm - you can fix this: it is open source ;-)' );
		}
		$this->client		= $client;
		$this->session		= $this->env->getSession();
	}
}
