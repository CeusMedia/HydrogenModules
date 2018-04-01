<?php
class Hook_Provision/* extends CMF_Hydrogen_Hook*/{

	/**
	 *	@todo    		extract to (atm-not-yet-existing) abstract framework hook class
	 */
	static protected function getModuleConfig( $env, $moduleKey ){
		$key	= 'modules.'.strtolower( $moduleKey ).'.';
		return $env->getConfig()->getAll( $key, TRUE );
	}

	static public function onAppDispatch( $env, $context, $module, $data ){
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$modules		= $env->getModules();
		$resource		= new Resource_Provision_Client( $env );

		if( !$moduleConfig->get( 'enabled' ) )
			return;

		$hasCache	= $modules->has( 'Resource_Cache' );
		if( $hasCache )
			$cache		=  new Model_Cache( $env );

		if( $modules->has( 'Resource_Authentication' ) ){
			$auth		= Logic_Authentication::getInstance( $env );
			if( $auth->isAuthenticated() ){
				try{
					$userId		= $auth->getCurrentUserId();
					if( $hasCache && $cache->has( 'userId-'.$userId, 'Provision.userLicenseKey' ) )
						return;
					$response	= $resource->getUserLicenseKey( $userId );
					if( $response->code !== 2 ){
						$path		= $env->getRequest()->get( '__path' );
						$freePaths	= $moduleConfig->get( 'licenseFreePaths' );
						$regex		= "/^(".str_replace( ',', '|', preg_quote( $freePaths, '/' ) ).")/";
						if( preg_match( $regex, $path ) )
							return;
						$language	= $env->getLanguage();
						$words		= (object) $language->getWords( 'provision' );
						$env->getMessenger()->noteNotice( $words->onAppDispatch['noticeAccessDenied'] );
						$controller	= new Controller_Provision( $env, FALSE );
						$controller->restart( 'provision/status' );

/*						$env->getRequest()->set( 'controller', 'provision' );
						$env->getRequest()->set( 'action', 'status' );
						$env->getRequest()->set( 'arguments', array( $userId ) );*/
					}
					if( $hasCache )
						$cache->set( 'userId-'.$userId, TRUE, 'Provision.userLicenseKey' );
				}
				catch( Exception $e ){
					$env->getMessenger()->noteError( 'Der Provision-Server ist zur Zeit nicht erreichbar ('.$e->getMessage().'). Bitte später noch einmal probieren!' );
				}
			}
		}
	}

	/**
	 *	Order license, assign key and active if a free license has been selected during registration.
	 *	Works, if selected license is a free single license and not already used by user.
	 *	@static
	 *	@access		public
	 *	@param		$env		object			Environment object
	 *	@param		$context	object			Hook call context object
	 *	@param		$module		object			Configuration of calling module
	 *	@param		$data		array 			Data provided by hook call
	 *	@return		void
	 */
	static public function onAuthAfterConfirm( $env, $context, $module, $data ){
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$resource		= new Resource_Provision_Client( $env );
		if( !$moduleConfig->get( 'enabled' ) )
			return;
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;

		$registerLicense	= $env->getSession()->get( 'register_license' );
		if( !$registerLicense || empty( $data['userId'] ) )
			return;

		$productId		= $moduleConfig->get( 'productId' );
		$licenses		= $resource->getProductLicenses( $productId );
		foreach( $licenses as $license ){
			if( $license->productLicenseId != $registerLicense )
				continue;
			try{
//				$modelUser		= new Model_User( $env );
//				$user			= $modelUser->get( $data['userId'] );
//				if( !$user )
//					return;
				$license	= $resource->getLicense( $registerLicense );
				if( (float) $license->price > 0 || $license->users > 1 )
					return;
				$postData	= array(
					'userId'			=> $data['userId'],
//					'password'			=> $user->password,											//  @todo GET USER PASSWORD
					'productLicenseId'	=> $license->productLicenseId,
					'assign'			=> TRUE,
					'activate'			=> TRUE,
				);
				if( $resource->request( 'provision/rest/orderLicense', $postData ) )
					$env->getMessenger()->noteSuccess( 'Die Lizenz "'.$license->title.'" wurde aktiviert.' );
			}
			catch( Exception $e ){
				$env->getMessenger()->noteFailure( 'Die Aktivierung der Lizenz "'.$license->title.'" ist fehlgeschlagen.' );
			}
		}
	}

	/**
	 *	@deprecated		if combination of add-free-license-after-confirm and redirect to account-status-on-app-dispatch is used
	 *	@todo 			kriss: remove if not needed or keep as fallback if upper case is not configured (needs to be configurable)
	 */
	static public function onAuthCheckBeforeLogin( $env, $context, $module, $data ){
return;
//		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
//		if( !$moduleConfig->get( 'enabled' ) )
//			return;
		$resource		= new Resource_Provision_Client( $env );
		if( !empty( $data['userId'] ) ){
			try{
				$response	= $resource->getUserLicenseKey( $data['userId'] );
				if( $response->code !== 2 ){
					$context->restart( 'provision/status/'.$data['userId'] );
					return FALSE;
				}
			}
			catch( Exception $e ){
				$env->getMessenger()->noteError( '__Der Provision-Server ist zur Zeit nicht erreichbar ('.$e->getMessage().'). Bitte später noch einmal probieren!' );
			}
		}
	}

	static public function onAuthCheckBeforeRegister( $env, $context, $module, $data ){
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		if( !$moduleConfig->get( 'enabled' ) )
			return;
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;
		$env->getSession()->set( 'register_license', $env->getRequest()->get( 'license' ) );
	}

	static public function onRenderRegisterFormExtensions( $env, $context, $module, $data ){
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$resource		= new Resource_Provision_Client( $env );
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;

		$response	= $resource->getProductLicenses( $moduleConfig->get( 'productId' ) );

		$body	= '';
		$list	= array();
		foreach( $response as $license ){
			$check	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'radio',
				'name'	=> 'license',
				'value'	=> $license->productLicenseId,
			) );
			$content	= implode( '<br/>', array(
				$license->title,
				UI_HTML_Tag::create( 'small', $license->price.' / '.$license->duration ),
				$check,
			) );
			$label	= UI_HTML_Tag::create( 'label', $content, array(
				'class'	=> 'btn btn-large',
				'style'	=> 'text-align: center',
			) );
			$list[]	= UI_HTML_Tag::create( 'div', $label, array( 'class' => 'span4' ) );
			if( count( $list ) % 3 === 0 ){
				$body	.= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'row-fluid' ) );
				$list	= array();
			}
		}
		if( count( $list ) )
			$body	.= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'row-fluid' ) );
		return UI_HTML_Tag::create( 'div', $body, array( 'id' => 'form-register-extension-accounting' ) );
	}
}
