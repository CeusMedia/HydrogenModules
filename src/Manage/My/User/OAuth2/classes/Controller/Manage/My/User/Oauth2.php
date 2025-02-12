<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_My_User_Oauth2 extends Controller
{
	protected MessengerResource $messenger;
	protected Model_Oauth_Provider $modelProvider;
	protected Model_Oauth_User $modelUserOauth;
	protected Logic_Authentication $logicAuth;

	public function add( string $providerId ): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$provider	= $this->modelProvider->get( $providerId );
		$client		= $this->getProviderObject( $providerId );
		$words		= (object) $this->getWords( 'add' );
		if( ( $error = $request->get( 'error' ) ) ){
			$this->env->getLog()->log( 'error', $error, $client );
			$this->messenger->noteError( $words->msgErrorResponded, $provider->title, $error );
			$this->restart( NULL, TRUE );
//			$this->restart( $authUrl, FALSE, NULL, TRUE );
		}
		else if( ( $code = $request->get( 'code' ) ) ){
			$state		= $request->get( 'state' );
			if( $state != $session->get( 'oauth2_state' ) ){
				$this->env->getLog()->log( 'error', 'invalid_state', $client );
				$this->messenger->noteError( $words->msgErrorInvalidState, $provider->title );
				$this->restart( NULL, TRUE );
			}
			try{
				$token	= $client->getAccessToken( 'authorization_code', ['code' => $code] );
				$user	= $client->getResourceOwner( $token );
				$exists	= $this->modelUserOauth->getByIndex( 'oauthId', $user->getId() );
				if( $exists ){
					$this->messenger->noteError( $words->msgErrorAlreadyConnected, $provider->title );
					$this->restart( NULL, TRUE );
				}
				$this->modelUserOauth->add( [
					'oauthProviderId'	=> $providerId,
					'oauthId'			=> $user->getId(),
					'localUserId'		=> $this->logicAuth->getCurrentUserId(),
					'timestamp'			=> time(),
				] );
				$this->messenger->noteSuccess( $words->msgSuccess, $provider->title );
			}
			catch( Exception $e ){
				$this->env->getLog()->log( 'error', $e->getMessage(), $client );
				$this->env->getLog()->logException( $e, $this );
				$this->messenger->noteError( $words->msgException, $provider->title );
			}
			$this->restart( NULL, TRUE );
		}
		else{
			$scopes	= [];
			if( $provider->composerPackage === "adam-paterson/oauth2-slack" )
				$scopes	= ['scope' => ['identity.basic']];
			else if( $provider->composerPackage === "stevenmaguire/oauth2-paypal" )
				$scopes	= ['scope' => ['openid', 'profile', 'email', 'phone', 'address']];
			else if( $provider->composerPackage === "omines/oauth2-gitlab" )
				$scopes	= ['scope' => ['read_user']];
			$authUrl	= $client->getAuthorizationUrl( $scopes );
			$session->set( 'oauth2_state', $client->getState() );
			$this->restart( $authUrl, FALSE, NULL, TRUE );
		}
	}

	public function index(): void
	{
		$providers	= $this->modelProvider->getAll(
			array( 'status' => Model_Oauth_Provider::STATUS_ACTIVE ),
			array( 'rank' => 'ASC' )
		);
		$list	= [];
		foreach( $providers as $provider )
			$list[$provider->oauthProviderId]	= $provider;
		$this->addData( 'providers', $list );

		$relations	= $this->modelUserOauth->getAll(
			['localUserId' => $this->logicAuth->getCurrentUserId()],
			['oauthUserId' => 'ASC']
		);
		$list	= [];
		foreach( $relations as $relation )
			$list[$relation->oauthProviderId]	= $relation;
		$this->addData( 'relations', $list );
	}

	public function remove( string $providerId ): void
	{
		$words		= (object) $this->getWords( 'remove' );
		$provider	= $this->checkProvider( $providerId );
		$indices	= array(
			'oauthProviderId'	=> $providerId,
			'localUserId'		=> $this->logicAuth->getCurrentUserId(),
		);
		$relation	= $this->modelUserOauth->getByIndices( $indices );
		if( !$relation ){
			$this->messenger->noteError( $words->msgErrorNotConnected, $provider->title );
			$this->restart( NULL, TRUE );
		}
		$this->modelUserOauth->remove( $relation->oauthUserId );
		$this->messenger->noteSuccess( $words->msgSuccess, $provider->title );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->messenger		= $this->env->getMessenger();
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
		$this->modelUserOauth	= new Model_Oauth_User( $this->env );
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
	}

	protected function checkProvider( string $providerId, bool $strict = TRUE ): ?object
	{
		if( $provider = $this->modelProvider->get( $providerId ) )
			return $provider;
		if( $strict )
			throw new RangeException( 'Invalid provider ID' );
		return NULL;
	}

	protected function getProviderObject( string $providerId ): object
	{
		$provider	= $this->checkProvider( $providerId );
		if( !class_exists( $provider->className ) )
			throw new RuntimeException( 'OAuth2 provider class is not existing: '.$provider->className );
		$options		= [
			'clientId'		=> $provider->clientId,
			'clientSecret'	=> $provider->clientSecret,
			'redirectUri'	=> $this->env->url.'manage/my/user/oauth2/add/'.$providerId,
		];
		if( $provider->options )
			$options	= array_merge( $options, json_decode( $provider->options, TRUE ) );
		return ObjectFactory::createObject( $provider->className, [$options] );
	}
}
