<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Provision extends Hook
{
	public function onAppDispatch(): void
	{
		$moduleConfig	= self::getModuleConfig( $this->env, 'Resource_Provision' );
		$modules		= $this->env->getModules();
		$resource		= new Resource_Provision_Client( $this->env );

		if( !$moduleConfig->get( 'active' ) )
			return;

		$hasCache	= $modules->has( 'Resource_Cache' );
		if( $hasCache )
			$cache		=  new Model_Cache( $this->env );

		if( $modules->has( 'Resource_Authentication' ) ){
			$auth		= Logic_Authentication::getInstance( $this->env );
			if( $auth->isAuthenticated() ){
				try{
					$userId		= $auth->getCurrentUserId();
					if( $hasCache && $cache->has( 'userId-'.$userId, 'Provision.userLicenseKey' ) )
						return;
					$response	= $resource->getUserLicenseKey( $userId );
					if( $response->code !== 2 ){
						$path		= $this->env->getRequest()->get( '__path' );
						$freePaths	= $moduleConfig->get( 'licenseFreePaths' );
						$regex		= "/^(".str_replace( ',', '|', preg_quote( $freePaths, '/' ) ).")/";
						if( preg_match( $regex, $path ) )
							return;
						$language	= $this->env->getLanguage();
						$words		= (object) $language->getWords( 'provision' );
						$this->env->getMessenger()->noteNotice( $words->onAppDispatch['noticeAccessDenied'] );
						self::restart( $this->env, 'provision/status' );

/*						$env->getRequest()->set( '__controller', 'provision' );
						$env->getRequest()->set( '__action', 'status' );
						$env->getRequest()->set( '__arguments', [$userId] );*/
					}
					if( $hasCache )
						$cache->set( 'userId-'.$userId, TRUE, 'Provision.userLicenseKey' );
				}
				catch( Exception $e ){
					$message	= 'Der Provision-Server ist zur Zeit nicht erreichbar (%s). Bitte später noch einmal probieren!';
					$this->env->getMessenger()->noteError( sprintf( $message, $e->getMessage() ) );
				}
			}
		}
	}

	/**
	 *	Order license, assign key and active if a free license has been selected during registration.
	 *	Works, if selected license is a free single license and not already used by user.
	 *	@access		public
	 *	@return		void
	 */
	public function onAuthAfterConfirm(): void
	{
		$data			= $this->getPayload() ?? [];
		$moduleConfig	= self::getModuleConfig( $this->env, 'Resource_Provision' );
		$productId		= $moduleConfig->get( 'productId' );

		if( !$moduleConfig->get( 'active' ) )
			return;
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;

		$registerLicense	= $this->env->getSession()->get( 'register_license' );
		if( !$registerLicense || empty( $data['userId'] ) )
			return;

		$resource	= new Resource_Provision_Client( $this->env );
		$licenses	= $resource->getProductLicenses( $productId );
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
				$postData	= [
					'userId'			=> $data['userId'],
//					'password'			=> $user->password,											//  @todo GET USER PASSWORD
					'productLicenseId'	=> $license->productLicenseId,
					'assign'			=> TRUE,
					'activate'			=> TRUE,
				];
				if( $resource->request( 'provision/rest/orderLicense', $postData ) )
					$this->env->getMessenger()->noteSuccess( 'Die Lizenz "'.$license->title.'" wurde aktiviert.' );
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteFailure( 'Die Aktivierung der Lizenz "'.$license->title.'" ist fehlgeschlagen.' );
			}
		}
	}

	/**
	 *	@deprecated		if combination of add-free-license-after-confirm and redirect to account-status-on-app-dispatch is used
	 *	@todo 			 remove if not needed or keep as fallback if upper case is not configured (needs to be configurable)
	 */
	public function onAuthCheckBeforeLogin(): void
	{
return;
//		$moduleConfig	= self::getModuleConfig( $env, 'Resource_Provision' );
//		if( !$moduleConfig->get( 'active' ) )
//			return;
		$data			= $this->getPayload() ?? [];
		$resource		= new Resource_Provision_Client( $this->env );
		if( !empty( $data['userId'] ) ){
			try{
				$response	= $resource->getUserLicenseKey( $data['userId'] );
				if( $response->code !== 2 ){
					$context->restart( 'provision/status/'.$data['userId'] );
					return;
				}
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteError( '__Der Provision-Server ist zur Zeit nicht erreichbar ('.$e->getMessage().'). Bitte später noch einmal probieren!' );
			}
		}
	}

	public function onAuthCheckBeforeRegister(): void
	{
		$moduleConfig	= self::getModuleConfig( $this->env, 'Resource_Provision' );
		if( !$moduleConfig->get( 'active' ) )
			return;
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return;
		$this->env->getSession()->set( 'register_license', $this->env->getRequest()->get( 'license' ) );
	}

	public function onRenderRegisterFormExtensions(): string|NULL
	{
		$moduleConfig	= self::getModuleConfig( $this->env, 'Resource_Provision' );
		$resource		= new Resource_Provision_Client( $this->env );
		if( $moduleConfig->get( 'mode' ) === "OAuth" )
			return NULL;

		$response	= $resource->getProductLicenses( $moduleConfig->get( 'productId' ) );

		$body	= '';
		$list	= [];
		foreach( $response as $license ){
			$check	= HtmlTag::create( 'input', NULL, [
				'type'	=> 'radio',
				'name'	=> 'license',
				'value'	=> $license->productLicenseId,
			] );
			$content	= implode( '<br/>', [
				$license->title,
				HtmlTag::create( 'small', $license->price.' / '.$license->duration ),
				$check,
			] );
			$label	= HtmlTag::create( 'label', $content, [
				'class'	=> 'btn btn-large',
				'style'	=> 'text-align: center',
			] );
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
