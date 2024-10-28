<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Exception\MissingExtension as NotSupportedExtension;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Authentication_Backend_Rest extends Logic implements Logic_Authentication_BackendInterface
{
	protected ?Resource_REST_Client $client;
	protected Dictionary $session;

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
		$result	= $this->client->post( 'authenticate', $parameters );
		return $result;
	}

	public function checkUsername( string $username )
	{
		$parameters	= ['username' => $username];
		return $this->client->post( 'username/check', $parameters )->data;
	}

	public function clearCurrentUser(): void
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
	public function confirm( int|string $userId, string $token )
	{
		$parameters	= [
			'userId'	=> $userId,
			'token'		=> $token,
		];
		$result	= $this->client->post( 'confirm', $parameters )->data;
		return $result;
	}

	public function getCurrentRole( bool $strict = TRUE ): NULL|object
	{
return NULL;
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

	public function getCurrentRoleId( bool $strict = TRUE ): int|string|NULL
	{
return NULL;
		if( !$this->isAuthenticated() ){
			if( $strict )
				throw new RuntimeException( 'No user authenticated' );
			return NULL;
		}
		return $this->session->get( 'auth_role_id');
	}

	public function getCurrentUser( bool $strict = TRUE, bool $withRole = FALSE ): ?object
	{
		$userId	= $this->getCurrentUserId( $strict );
		if( $userId ){
			$user	= $this->client->post( 'user/get', [$userId] );
			if( $user ){
				$user->role	= $withRole ? $this->getCurrentRole() : NULL;
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
		$this->session->set( 'auth_status', Logic_Authentication::STATUS_AUTHENTICATED );
		return $this;
	}

	public function setIdentifiedUser( object $user ): self
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
