<?php

//use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Auth_Local extends Hook
{
	protected static string $configPrefix	= 'module.resource_authentication_backend_local.';

	public function onAuthRegisterBackend(): void
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $this->env->getLanguage()->getWords( 'auth/local' );
		$this->context?->registerBackend( 'Local', 'local', $words['backend']['title'] );
	}

	public function onAuthRegisterLoginTab(): void
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $this->env->getLanguage()->getWords( 'auth/local' );					//  load words
		$rank		= $this->env->getConfig()->get( self::$configPrefix.'login.rank' );
		$this->context?->registerTab( 'auth/local/login', $words->login['tab'], $rank );				//  register main tab
	}

	/**
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function onGetRelatedUsers(): bool
	{
		if( !$this->env->getConfig()->get( self::$configPrefix.'relateToAllUsers' ) )
			return FALSE;
		$modelUser	= new Model_User( $this->env );
		$words		= $this->env->getLanguage()->getWords( 'auth/local' );
		$conditions	= ['status' => '> 0'];
		$users		= $modelUser->getAll( $conditions, ['username' => 'ASC'] );
		$this->payload['list']	= [(object) [
			'module'		=> $this->module,
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		]];
		return TRUE;
	}

/*	public function onPageApplyModules(): void
	{
		$userId		= (int) $this->env->getSession()->get( 'auth_user_id' );							//  get ID of current user (or zero)
		$cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		$remember	= (bool) $cookie->get( 'auth_remember' );
		$this->env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';					//  initialize Auth class with user ID
		$this->env->getPage()->js->addScriptOnReady( $script, 1 );									//  enlist script to be run on ready
	}*/

	/**
	 *	@return		void
	 *	@throws		Exception
	 */
	public function onViewRenderContent(): void
	{
		/** @var WebEnvironment $env */
		$env		= $this->env;
		$processor	= new Logic_Shortcode( $env );
		$shortCodes	= [
			'auth:local:panel:login'	=> [
				'oauth'		=> TRUE,
				'remember'	=> TRUE,
				'register'	=> TRUE,
			]
		];
		$processor->setContent( $this->payload['content'] );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Auth_Local_Panel_Login( $env );
			while( is_array( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				$attr['oauth']		= !( 'no' === strtolower( $attr['oauth'] ) );
				$attr['remember']	= !( 'no' === strtolower( $attr['remember'] ) );
				$attr['register']	= !( 'no' === strtolower( $attr['register'] ) );
				try{
					$helper->setUseOauth2( $attr['oauth'] );
					$helper->setUseRemember( $attr['remember'] );
					$helper->setUseRegister( $attr['register'] );
					$replacement	= $helper->render();
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}
}
