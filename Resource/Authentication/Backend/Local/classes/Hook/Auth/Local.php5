<?php
class Hook_Auth_Local extends CMF_Hydrogen_Hook
{
	static protected $configPrefix	= 'module.resource_authentication_backend_local.';

	public static function onAuthRegisterBackend( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words	= $env->getLanguage()->getWords( 'auth/local' );
		$context->registerBackend( 'Local', 'local', $words['backend']['title'] );
	}

	static public function onAuthRegisterLoginTab( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		if( !$env->getConfig()->get( self::$configPrefix.'active' ) )
			return;
		$words		= (object) $env->getLanguage()->getWords( 'auth/local' );					//  load words
		$rank		= $env->getConfig()->get( self::$configPrefix.'login.rank' );
		$context->registerTab( 'auth/local/login', $words->login['tab'], $rank );				//  register main tab
	}

	static public function onGetRelatedUsers( CMF_Hydrogen_Environment $env, $context, $module, $payload ){
		if( !$env->getConfig()->get( self::$configPrefix.'relateToAllUsers' ) )
			return;
		$modelUser	= new Model_User( $env );
		$words		= $env->getLanguage()->getWords( 'auth/local' );
		$conditions	= array( 'status' => '> 0' );
		$users		= $modelUser->getAll( $conditions, array( 'username' => 'ASC' ) );
		$payload->list	= array( (object) array(
			'module'		=> $module,
			'label'			=> $words['hook-getRelatedUsers']['label'],
			'count'			=> count( $users ),
			'list'			=> $users,
		) );
		return TRUE;
	}

/*	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$userId		= (int) $env->getSession()->get( 'auth_user_id' );							//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
		$remember	= (bool) $cookie->get( 'auth_remember' );
		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';					//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );									//  enlist script to be run on ready
	}*/

	public static function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$config		= $env->getConfig()->getAll( 'module.resource_auth.', TRUE );
		$processor	= new Logic_Shortcode( $env );
		$shortCodes	= array(
			'auth:local:panel:login'	=> array(
				'oauth'		=> TRUE,
				'remember'	=> TRUE,
				'register'	=> TRUE,
			)
		);
		$processor->setContent( $payload->content );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Auth_Local_Panel_Login( $env );
			while( is_array( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				$attr['oauth']		= strtolower( $attr['oauth'] ) === 'no' ? FALSE : TRUE;
				$attr['remember']	= strtolower( $attr['remember'] ) === 'no' ? FALSE : TRUE;
				$attr['register']	= strtolower( $attr['register'] ) === 'no' ? FALSE : TRUE;
				try{
					$helper->setUseOauth2( $attr['oauth'] );
					$helper->setUseRemember( $attr['remember'] );
					$helper->setUseRegister( $attr['register'] );
					$replacement	= (string) $helper->render();
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$payload->content	= $processor->getContent();
	}
}
