<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Provision extends Hook
{
	/**
	 *	@todo    		extract to (atm-not-yet-existing) abstract framework hook class
	 */
	static protected function getModuleConfig( Environment $env, $moduleKey )
	{
		$key	= 'modules.'.strtolower( $moduleKey ).'.';
		return $env->getConfig()->getAll( $key, TRUE );
	}

	static public function onAppDispatch( Environment $env, $context, $module, $data )
	{
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$modules		= $env->getModules();
		$resource		= new Resource_Provision_Client( $env );

		if( !$moduleConfig->get( 'active' ) )
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

/*						$env->getRequest()->set( '__controller', 'provision' );
						$env->getRequest()->set( '__action', 'status' );
						$env->getRequest()->set( '__arguments', [$userId] );*/
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
	static public function onAuthAfterConfirm( Environment $env, $context, $module, $data )
	{
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$resource		= new Resource_Provision_Client( $env );
		if( !$moduleConfig->get( 'active' ) )
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
	static public function onAuthCheckBeforeLogin( Environment $env, $context, $module, $data )
	{
return;
//		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
//		if( !$moduleConfig->get( 'active' ) )
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

	static public function onAuthCheckBeforeRegister( $env, $context, $module, $data )
	{
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		if( !$moduleConfig->get( 'active' ) )
			return;
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;
		$env->getSession()->set( 'register_license', $env->getRequest()->get( 'license' ) );
	}

	static public function onRenderRegisterFormExtensions( $env, $context, $module, $data )
	{
		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
		$resource		= new Resource_Provision_Client( $env );
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;

		$response	= $resource->getProductLicenses( $moduleConfig->get( 'productId' ) );

		$body	= '';
		$list	= [];
		foreach( $response as $license ){
			$check	= HtmlTag::create( 'input', NULL, array(
				'type'	=> 'radio',
				'name'	=> 'license',
				'value'	=> $license->productLicenseId,
			) );
			$content	= implode( '<br/>', array(
				$license->title,
				HtmlTag::create( 'small', $license->price.' / '.$license->duration ),
				$check,
			) );
			$label	= HtmlTag::create( 'label', $content, array(
				'class'	=> 'btn btn-large',
				'style'	=> 'text-align: center',
			) );
			$list[]	= HtmlTag::create( 'div', $label, ['class' => 'span4'] );
			if( count( $list ) % 3 === 0 ){
				$body	.= HtmlTag::create( 'div', $list, ['class' => 'row-fluid'] );
				$list	= [];
			}
		}
		if( count( $list ) )
			$body	.= HtmlTag::create( 'div', $list, ['class' => 'row-fluid'] );
		return HtmlTag::create( 'div', $body, ['id' => 'form-register-extension-accounting'] );
	}
}
